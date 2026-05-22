<?php

namespace Tests\Feature;

use App\Filament\Clusters\LeadCenter\Resources\Leads\LeadResource;
use App\Filament\Clusters\SystemSettings\Resources\AutomationRules\AutomationRuleResource;
use App\Models\Activity;
use App\Models\AssignmentRule;
use App\Models\AssignmentRuleCondition;
use App\Models\AuditLog;
use App\Models\AutomationAction;
use App\Models\AutomationRule;
use App\Models\Attachment;
use App\Models\BusinessNumberRule;
use App\Models\Contact;
use App\Models\CustomField;
use App\Models\Customer;
use App\Models\Department;
use App\Models\DuplicateRecord;
use App\Models\FieldHistory;
use App\Models\Lead;
use App\Models\LeadScoreRule;
use App\Models\Notification as CrmNotification;
use App\Models\Opportunity;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderPaymentPlan;
use App\Models\Payment;
use App\Models\Permission;
use App\Models\Pipeline;
use App\Models\Plan;
use App\Models\PriceBook;
use App\Models\PriceBookItem;
use App\Models\Product;
use App\Models\ProductGroup;
use App\Models\ProductSku;
use App\Models\Quote;
use App\Models\QuoteItem;
use App\Models\Role;
use App\Models\SalesTarget;
use App\Models\Setting;
use App\Models\Tenant;
use App\Models\TenantInvitation;
use App\Models\TenantSubscription;
use App\Models\User;
use App\Services\Crm\CustomerMergeService;
use App\Services\Crm\CustomerPoolService;
use App\Services\Crm\DataPortService;
use App\Services\Crm\LeadConversionService;
use App\Services\Crm\LeadScoringService;
use App\Services\Crm\NotificationService;
use App\Services\Crm\OpportunityStageService;
use App\Services\Crm\PlanLimitService;
use App\Services\Crm\QuotePdfService;
use App\Services\Crm\QuoteToOrderService;
use App\Services\Crm\QuoteVersionService;
use App\Services\Crm\SubscriptionLifecycleService;
use App\Services\Crm\TenantInvitationService;
use App\Support\CrmMetrics;
use App\Support\Filament\CustomFieldUi;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class CrmWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_lead_can_be_converted_to_customer_contact_and_opportunity(): void
    {
        $user = $this->user();
        $tenant = $this->tenant($user);

        $pipeline = Pipeline::create([
            'tenant_id' => $tenant->id,
            'name' => '默认管道',
            'is_default' => true,
            'is_active' => true,
        ]);

        $stage = $pipeline->stages()->create([
            'tenant_id' => $tenant->id,
            'name' => '初步沟通',
            'probability' => 20,
            'stage_type' => 'open',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $lead = Lead::create([
            'tenant_id' => $tenant->id,
            'company_name' => 'Acme',
            'contact_name' => 'Alice',
            'phone' => '13900000000',
            'email' => 'alice@example.com',
            'status' => 'working',
            'owner_user_id' => $user->id,
        ]);

        $this->actingAs($user);

        $customer = app(LeadConversionService::class)->convert($lead, true);

        $this->assertDatabaseHas('customers', [
            'id' => $customer->id,
            'tenant_id' => $tenant->id,
            'name' => 'Acme',
        ]);

        $this->assertDatabaseHas('contacts', [
            'tenant_id' => $tenant->id,
            'customer_id' => $customer->id,
            'name' => 'Alice',
        ]);

        $this->assertDatabaseHas('opportunities', [
            'tenant_id' => $tenant->id,
            'customer_id' => $customer->id,
            'pipeline_stage_id' => $stage->id,
        ]);

        $this->assertSame('converted', $lead->refresh()->status);
    }

    public function test_quote_can_be_calculated_and_converted_to_order_with_snapshots(): void
    {
        $user = $this->user();
        $tenant = $this->tenant($user);

        $customer = Customer::create([
            'tenant_id' => $tenant->id,
            'name' => 'Acme',
            'customer_type' => 'company',
            'lifecycle_stage' => 'active',
            'owner_user_id' => $user->id,
        ]);

        $group = ProductGroup::create(['tenant_id' => $tenant->id, 'name' => '服务']);
        $product = Product::create(['tenant_id' => $tenant->id, 'group_id' => $group->id, 'name' => 'CRM 服务', 'tax_rate' => 6]);
        $sku = ProductSku::create([
            'tenant_id' => $tenant->id,
            'product_id' => $product->id,
            'sku_code' => 'CRM-001',
            'price' => 1000,
            'cost_price' => 300,
            'stock' => 10,
            'is_active' => true,
        ]);

        $quote = Quote::create([
            'tenant_id' => $tenant->id,
            'quote_number' => 'QT-TEST',
            'title' => '测试报价',
            'customer_id' => $customer->id,
            'user_id' => $user->id,
            'status' => 'approved',
        ]);

        QuoteItem::create([
            'tenant_id' => $tenant->id,
            'quote_id' => $quote->id,
            'product_id' => $product->id,
            'product_sku_id' => $sku->id,
            'quantity' => 2,
        ]);

        $this->actingAs($user);

        Storage::fake('local');

        app(QuotePdfService::class)->generate($quote->refresh());
        Storage::disk('local')->assertExists($quote->refresh()->pdf_path);

        $order = app(QuoteToOrderService::class)->convert($quote->refresh());

        $this->assertSame('accepted', $quote->refresh()->status);
        $this->assertEquals('2000.00', $quote->total_amount);
        $this->assertEquals('1400.00', $quote->total_profit);

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'tenant_id' => $tenant->id,
            'quote_id' => $quote->id,
            'total_amount' => '2000.00',
        ]);

        $this->assertDatabaseHas('order_items', [
            'order_id' => $order->id,
            'product_name' => 'CRM 服务',
            'sku_code' => 'CRM-001',
            'quantity' => 2,
        ]);

        $this->assertDatabaseHas('activities', [
            'tenant_id' => $tenant->id,
            'customer_id' => $customer->id,
            'type' => 'system',
            'subject' => '报价转订单：'.$order->order_number,
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'tenant_id' => $tenant->id,
            'model_type' => Quote::class,
            'model_id' => $quote->id,
            'action' => 'quote_converted_to_order',
        ]);
    }

    public function test_customer_pool_and_merge_keep_history(): void
    {
        $user = $this->user();
        $tenant = $this->tenant($user);

        $source = Customer::create([
            'tenant_id' => $tenant->id,
            'name' => 'Source Co',
            'customer_type' => 'company',
            'lifecycle_stage' => 'active',
            'owner_user_id' => $user->id,
            'phone' => '13900000001',
        ]);

        $target = Customer::create([
            'tenant_id' => $tenant->id,
            'name' => 'Target Co',
            'customer_type' => 'company',
            'lifecycle_stage' => 'active',
            'owner_user_id' => $user->id,
        ]);

        Contact::create([
            'tenant_id' => $tenant->id,
            'customer_id' => $source->id,
            'name' => 'Alice',
        ]);

        $this->actingAs($user);

        app(CustomerPoolService::class)->releaseCustomer($source, '测试释放');
        $this->assertSame('pooled', $source->refresh()->lifecycle_stage);

        app(CustomerPoolService::class)->claimCustomer($source, $user);
        $this->assertSame($user->id, $source->refresh()->owner_user_id);

        app(CustomerMergeService::class)->merge($source, $target);

        $this->assertSoftDeleted('customers', ['id' => $source->id]);
        $this->assertDatabaseHas('contacts', ['customer_id' => $target->id, 'name' => 'Alice']);
        $this->assertDatabaseHas('merge_histories', ['source_id' => $source->id, 'target_id' => $target->id]);
        $this->assertDatabaseCount('customer_pool_histories', 2);
    }

    public function test_automation_rule_creates_task_when_lead_is_created(): void
    {
        $user = $this->user();
        $tenant = $this->tenant($user);
        $this->actingAs($user);

        $rule = AutomationRule::create([
            'tenant_id' => $tenant->id,
            'name' => '新线索自动任务',
            'trigger_type' => 'lead_created',
            'target_type' => 'lead',
            'conditions' => ['source' => '官网'],
            'is_active' => true,
        ]);

        AutomationAction::create([
            'tenant_id' => $tenant->id,
            'automation_rule_id' => $rule->id,
            'action_type' => 'create_task',
            'payload' => ['title' => '联系官网线索', 'due_days' => 2],
        ]);

        $lead = Lead::create([
            'tenant_id' => $tenant->id,
            'company_name' => 'Web Lead',
            'source' => '官网',
            'owner_user_id' => $user->id,
        ]);

        $this->assertDatabaseHas('tasks', [
            'tenant_id' => $tenant->id,
            'lead_id' => $lead->id,
            'title' => '联系官网线索',
            'assignee_id' => $user->id,
        ]);
    }

    public function test_lead_scoring_rules_explain_and_batch_recalculate_scores(): void
    {
        $user = $this->user();
        $tenant = $this->tenant($user);
        $this->actingAs($user);

        $lead = Lead::create([
            'tenant_id' => $tenant->id,
            'company_name' => '官网高意向',
            'source' => '官网',
            'phone' => '13900001111',
            'email' => 'web@example.com',
        ]);

        LeadScoreRule::create([
            'tenant_id' => $tenant->id,
            'name' => '官网来源加分',
            'field' => 'source',
            'operator' => 'eq',
            'value' => ['官网'],
            'score' => 40,
            'is_active' => true,
        ]);

        $count = app(LeadScoringService::class)->refreshMany($tenant->id);
        $lead->refresh();
        $explanation = app(LeadScoringService::class)->explain($lead);

        $this->assertSame(1, $count);
        $this->assertSame($explanation['total'], $lead->score);
        $this->assertSame(['官网来源加分'], collect($explanation['rules'])->pluck('name')->all());
        $this->assertGreaterThanOrEqual(60, $lead->score);
    }

    public function test_new_lead_is_auto_assigned_by_conditions_to_least_busy_sales(): void
    {
        $manager = $this->user();
        $salesA = $this->user();
        $salesB = $this->user();
        $tenant = $this->tenant($manager);
        $this->attachTenantUser($tenant, $salesA);
        $this->attachTenantUser($tenant, $salesB);
        $this->actingAs($manager);

        $department = Department::create([
            'tenant_id' => $tenant->id,
            'name' => '华东一部',
            'sort_order' => 1,
        ]);

        $department->users()->attach($salesA->id, ['tenant_id' => $tenant->id, 'is_leader' => false, 'main_department' => true]);
        $department->users()->attach($salesB->id, ['tenant_id' => $tenant->id, 'is_leader' => false, 'main_department' => true]);

        Lead::create([
            'tenant_id' => $tenant->id,
            'company_name' => '销售A已有线索',
            'source' => '展会',
            'owner_user_id' => $salesA->id,
            'status' => 'working',
        ]);

        $rule = AssignmentRule::create([
            'tenant_id' => $tenant->id,
            'name' => '官网线索分配',
            'target_type' => 'lead',
            'method' => 'least_busy',
            'department_id' => $department->id,
            'max_per_user_daily' => 10,
            'priority' => 100,
            'is_active' => true,
        ]);

        AssignmentRuleCondition::create([
            'tenant_id' => $tenant->id,
            'assignment_rule_id' => $rule->id,
            'field' => 'source',
            'operator' => 'eq',
            'value' => ['官网'],
        ]);

        $lead = Lead::create([
            'tenant_id' => $tenant->id,
            'company_name' => '官网待分配线索',
            'source' => '官网',
        ]);

        $this->assertSame($salesB->id, $lead->refresh()->owner_user_id);
        $this->assertSame('working', $lead->status);
        $this->assertDatabaseHas('customer_pool_histories', [
            'tenant_id' => $tenant->id,
            'target_type' => Lead::class,
            'target_id' => $lead->id,
            'action' => 'claim',
            'to_user_id' => $salesB->id,
        ]);
        $this->assertDatabaseHas('audit_logs', [
            'tenant_id' => $tenant->id,
            'model_type' => Lead::class,
            'model_id' => $lead->id,
            'action' => 'lead_auto_assigned',
        ]);
    }

    public function test_import_export_records_success_and_failed_rows(): void
    {
        $user = $this->user();
        $tenant = $this->tenant($user);

        Storage::fake('local');
        Storage::disk('local')->put('imports/leads.csv', "company_name,contact_name,phone\nAcme,Alice,13900000002\n,,\n");

        $import = app(DataPortService::class)->import($tenant->id, $user, 'leads', 'imports/leads.csv');

        $this->assertSame(2, $import->processed_rows);
        $this->assertSame(1, $import->successful_rows);
        $this->assertDatabaseHas('leads', ['tenant_id' => $tenant->id, 'company_name' => 'Acme']);
        $this->assertDatabaseCount('failed_import_rows', 1);

        Customer::create([
            'tenant_id' => $tenant->id,
            'name' => 'Export Co',
            'customer_type' => 'company',
            'lifecycle_stage' => 'active',
        ]);

        $export = app(DataPortService::class)->export($tenant->id, $user, 'customers');

        $this->assertSame(1, $export->total_rows);
        Storage::disk('local')->assertExists($export->file_name);
    }

    public function test_customer_custom_field_is_saved_listed_and_filterable(): void
    {
        $user = $this->user();
        $tenant = $this->tenant($user);

        app()->instance('currentTenant', $tenant);
        $this->actingAs($user);

        CustomField::create([
            'tenant_id' => $tenant->id,
            'model_type' => 'customer',
            'group_name' => '销售字段',
            'type' => 'textarea',
            'identifier' => 'customer_level_note',
            'name' => '客户等级备注',
            'is_visible' => true,
            'is_required' => true,
            'is_filterable' => true,
            'is_list_visible' => true,
        ]);

        Customer::create([
            'tenant_id' => $tenant->id,
            'name' => '等级客户',
            'customer_type' => 'company',
            'lifecycle_stage' => 'active',
            'custom_fields' => ['customer_level_note' => '战略客户，年度重点维护'],
        ]);

        $this->assertSame(['customer_level_note'], CustomFieldUi::fields('customer')->pluck('identifier')->all());
        $this->assertCount(1, CustomFieldUi::tableColumns('customer'));
        $this->assertCount(1, CustomFieldUi::tableFilters('customer'));
        $this->assertSame(
            ['等级客户'],
            CustomFieldUi::whereJsonValue(Customer::query(), 'customer_level_note', '战略客户', exact: false)->pluck('name')->all(),
        );
    }

    public function test_opportunity_stage_required_fields_block_progress(): void
    {
        $user = $this->user();
        $tenant = $this->tenant($user);
        $this->actingAs($user);

        $pipeline = Pipeline::create([
            'tenant_id' => $tenant->id,
            'name' => '默认管道',
            'is_default' => true,
            'is_active' => true,
        ]);

        $fromStage = $pipeline->stages()->create([
            'tenant_id' => $tenant->id,
            'name' => '初步沟通',
            'probability' => 20,
            'stage_type' => 'open',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $quoteStage = $pipeline->stages()->create([
            'tenant_id' => $tenant->id,
            'name' => '方案报价',
            'probability' => 60,
            'stage_type' => 'open',
            'sort_order' => 2,
            'is_active' => true,
            'required_fields' => ['amount', 'expected_close_date'],
        ]);

        $customer = Customer::create([
            'tenant_id' => $tenant->id,
            'name' => '缺字段客户',
            'customer_type' => 'company',
            'lifecycle_stage' => 'active',
            'owner_user_id' => $user->id,
        ]);

        $opportunity = Opportunity::create([
            'tenant_id' => $tenant->id,
            'customer_id' => $customer->id,
            'pipeline_id' => $pipeline->id,
            'pipeline_stage_id' => $fromStage->id,
            'name' => '缺字段商机',
            'amount' => 0,
            'probability' => 20,
            'forecast_category' => 'pipeline',
            'responsible_user_id' => $user->id,
        ]);

        try {
            app(OpportunityStageService::class)->move($opportunity, $quoteStage);
            $this->fail('缺少阶段必填字段时不应推进商机。');
        } catch (ValidationException $exception) {
            $message = collect($exception->errors())->flatten()->join(' ');
            $this->assertStringContainsString('金额', $message);
            $this->assertStringContainsString('预计成交日期', $message);
        }
    }

    public function test_quote_price_book_sets_unit_price_and_low_margin_requires_approval(): void
    {
        $user = $this->user();
        $tenant = $this->tenant($user);
        $this->actingAs($user);

        Setting::create([
            'tenant_id' => $tenant->id,
            'key' => 'quote_approval_rules',
            'value' => ['min_profit_margin' => 20],
        ]);

        $customer = Customer::create([
            'tenant_id' => $tenant->id,
            'name' => '价格客户',
            'customer_type' => 'company',
            'lifecycle_stage' => 'active',
            'owner_user_id' => $user->id,
        ]);

        $group = ProductGroup::create(['tenant_id' => $tenant->id, 'name' => '服务']);
        $product = Product::create(['tenant_id' => $tenant->id, 'group_id' => $group->id, 'name' => '实施服务', 'tax_rate' => 6]);
        $sku = ProductSku::create([
            'tenant_id' => $tenant->id,
            'product_id' => $product->id,
            'sku_code' => 'SVC-001',
            'price' => 1000,
            'cost_price' => 720,
            'stock' => 10,
            'is_active' => true,
        ]);
        $priceBook = PriceBook::create([
            'tenant_id' => $tenant->id,
            'name' => '大客户价',
            'code' => 'VIP',
            'currency' => 'CNY',
            'is_active' => true,
        ]);
        PriceBookItem::create([
            'tenant_id' => $tenant->id,
            'price_book_id' => $priceBook->id,
            'product_sku_id' => $sku->id,
            'price' => 800,
            'min_price' => 760,
        ]);

        $quote = Quote::create([
            'tenant_id' => $tenant->id,
            'title' => '大客户报价',
            'customer_id' => $customer->id,
            'user_id' => $user->id,
            'price_book_id' => $priceBook->id,
            'status' => 'draft',
        ]);

        $item = QuoteItem::create([
            'tenant_id' => $tenant->id,
            'quote_id' => $quote->id,
            'product_sku_id' => $sku->id,
            'quantity' => 1,
        ]);

        $this->assertEquals('800.00', $item->refresh()->unit_price);

        try {
            app(QuoteToOrderService::class)->convert($quote->refresh());
            $this->fail('低毛利报价不应直接转订单。');
        } catch (ValidationException $exception) {
            $this->assertStringContainsString('毛利率', collect($exception->errors())->flatten()->join(' '));
        }

        $this->assertSame('pending_approval', $quote->refresh()->status);
        $this->assertDatabaseHas('quote_approval_requests', [
            'tenant_id' => $tenant->id,
            'quote_id' => $quote->id,
            'status' => 'pending',
        ]);
    }

    public function test_order_payment_plan_tracks_completed_payments(): void
    {
        $user = $this->user();
        $tenant = $this->tenant($user);
        $this->actingAs($user);

        $customer = Customer::create([
            'tenant_id' => $tenant->id,
            'name' => '回款客户',
            'customer_type' => 'company',
            'lifecycle_stage' => 'active',
            'owner_user_id' => $user->id,
        ]);

        $order = Order::create([
            'tenant_id' => $tenant->id,
            'order_number' => 'SO-PLAN',
            'customer_id' => $customer->id,
            'employee_id' => $user->id,
            'total_amount' => 10000,
            'order_source' => 'sales_entry',
            'order_status' => 'confirmed',
            'payment_status' => 'unpaid',
            'ordered_at' => now(),
        ]);

        $plan = OrderPaymentPlan::create([
            'tenant_id' => $tenant->id,
            'order_id' => $order->id,
            'plan_date' => now()->addDays(10)->toDateString(),
            'plan_amount' => 3000,
            'status' => 'pending',
        ]);

        Payment::create([
            'tenant_id' => $tenant->id,
            'order_id' => $order->id,
            'payment_plan_id' => $plan->id,
            'amount' => 3000,
            'status' => 'completed',
            'received_at' => now(),
        ]);

        $this->assertEquals('3000.00', $plan->refresh()->received_amount);
        $this->assertSame('paid', $plan->status);
        $this->assertSame('partial_paid', $order->refresh()->payment_status);
    }

    public function test_quote_versions_expire_old_versions_and_only_one_version_can_convert(): void
    {
        $user = $this->user();
        $tenant = $this->tenant($user);
        $this->actingAs($user);

        $pipeline = Pipeline::create([
            'tenant_id' => $tenant->id,
            'name' => '默认管道',
            'is_default' => true,
            'is_active' => true,
        ]);

        $stage = $pipeline->stages()->create([
            'tenant_id' => $tenant->id,
            'name' => '报价',
            'probability' => 60,
            'stage_type' => 'open',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $customer = Customer::create([
            'tenant_id' => $tenant->id,
            'name' => '版本客户',
            'customer_type' => 'company',
            'lifecycle_stage' => 'active',
            'owner_user_id' => $user->id,
        ]);

        $opportunity = Opportunity::create([
            'tenant_id' => $tenant->id,
            'customer_id' => $customer->id,
            'pipeline_id' => $pipeline->id,
            'pipeline_stage_id' => $stage->id,
            'name' => '版本商机',
            'amount' => 1000,
            'probability' => 60,
            'forecast_category' => 'pipeline',
            'responsible_user_id' => $user->id,
        ]);

        $group = ProductGroup::create(['tenant_id' => $tenant->id, 'name' => '服务']);
        $product = Product::create(['tenant_id' => $tenant->id, 'group_id' => $group->id, 'name' => '版本服务', 'tax_rate' => 6]);
        $sku = ProductSku::create([
            'tenant_id' => $tenant->id,
            'product_id' => $product->id,
            'sku_code' => 'VER-001',
            'price' => 1000,
            'cost_price' => 300,
            'stock' => 10,
            'is_active' => true,
        ]);

        $quoteV1 = Quote::create([
            'tenant_id' => $tenant->id,
            'quote_number' => 'QT-VERSION',
            'title' => '版本报价',
            'customer_id' => $customer->id,
            'opportunity_id' => $opportunity->id,
            'user_id' => $user->id,
            'status' => 'approved',
        ]);

        QuoteItem::create([
            'tenant_id' => $tenant->id,
            'quote_id' => $quoteV1->id,
            'product_id' => $product->id,
            'product_sku_id' => $sku->id,
            'quantity' => 1,
        ]);

        $quoteV2 = app(QuoteVersionService::class)->createNewVersion($quoteV1);

        $this->assertSame(2, $quoteV2->version);
        $this->assertSame('expired', $quoteV1->refresh()->status);
        $this->assertSame($quoteV1->id, $quoteV2->source_quote_id);
        $this->assertCount(1, $quoteV2->items);

        $quoteV2->forceFill(['status' => 'approved'])->save();
        app(QuoteToOrderService::class)->convert($quoteV2->refresh());

        $this->assertSame('accepted', $quoteV2->refresh()->status);

        $quoteV3 = Quote::create([
            'tenant_id' => $tenant->id,
            'quote_number' => 'QT-VERSION-V3',
            'version' => 3,
            'source_quote_id' => $quoteV1->id,
            'title' => '版本报价 V3',
            'customer_id' => $customer->id,
            'opportunity_id' => $opportunity->id,
            'user_id' => $user->id,
            'status' => 'approved',
        ]);

        try {
            app(QuoteToOrderService::class)->convert($quoteV3);
            $this->fail('同一商机已有接受报价时，其他版本不能转订单。');
        } catch (ValidationException $exception) {
            $this->assertStringContainsString('已有已接受报价', collect($exception->errors())->flatten()->join(' '));
        }
    }

    public function test_import_duplicate_phone_goes_to_duplicate_pool(): void
    {
        $user = $this->user();
        $tenant = $this->tenant($user);

        Lead::create([
            'tenant_id' => $tenant->id,
            'company_name' => '重复线索',
            'phone' => '13800000000',
        ]);

        Storage::fake('local');
        Storage::disk('local')->put('imports/leads.csv', "company_name,phone\n新重复,138 0000 0000\n");

        $import = app(DataPortService::class)->import($tenant->id, $user, 'leads', 'imports/leads.csv');

        $this->assertSame(1, $import->processed_rows);
        $this->assertSame(0, $import->successful_rows);
        $this->assertDatabaseHas('duplicate_records', [
            'tenant_id' => $tenant->id,
            'target_type' => 'lead',
            'matched_type' => 'lead',
            'field_name' => 'phone',
            'field_value' => '13800000000',
            'status' => 'pending',
        ]);
    }

    public function test_customer_merge_can_keep_selected_conflicting_fields(): void
    {
        $user = $this->user();
        $tenant = $this->tenant($user);
        $this->actingAs($user);

        $source = Customer::create([
            'tenant_id' => $tenant->id,
            'name' => '来源客户',
            'customer_type' => 'company',
            'lifecycle_stage' => 'active',
            'phone' => '13800000001',
            'email' => 'source@example.com',
        ]);

        $target = Customer::create([
            'tenant_id' => $tenant->id,
            'name' => '目标客户',
            'customer_type' => 'company',
            'lifecycle_stage' => 'active',
            'phone' => '13800000002',
            'email' => 'target@example.com',
        ]);

        $preview = app(CustomerMergeService::class)->preview($source, $target);
        $this->assertTrue($preview['fields']['phone']['conflict']);

        $merged = app(CustomerMergeService::class)->mergeWithFields($source, $target, [
            'phone' => 'source',
            'email' => 'target',
        ]);

        $this->assertSame('13800000001', $merged->phone);
        $this->assertSame('target@example.com', $merged->email);
        $this->assertSoftDeleted('customers', ['id' => $source->id]);
    }

    public function test_order_attachments_are_grouped_by_business_category(): void
    {
        $user = $this->user();
        $tenant = $this->tenant($user);

        $customer = Customer::create([
            'tenant_id' => $tenant->id,
            'name' => '附件客户',
            'customer_type' => 'company',
            'lifecycle_stage' => 'active',
        ]);

        $order = Order::create([
            'tenant_id' => $tenant->id,
            'order_number' => 'SO-ATTACH',
            'customer_id' => $customer->id,
            'total_amount' => 1000,
            'order_source' => 'sales_entry',
            'order_status' => 'confirmed',
            'payment_status' => 'unpaid',
            'ordered_at' => now(),
        ]);

        foreach (['contract', 'payment_voucher', 'expense_voucher'] as $category) {
            Attachment::create([
                'tenant_id' => $tenant->id,
                'path' => 'tenants/'.$tenant->id.'/orders/'.$category.'.pdf',
                'disk' => 'local',
                'user_id' => $user->id,
                'model_type' => Order::class,
                'model_id' => $order->id,
                'category' => $category,
                'name' => $category,
            ]);
        }

        $this->assertSame(
            ['contract', 'expense_voucher', 'payment_voucher'],
            $order->attachments()->pluck('category')->sort()->values()->all(),
        );
    }

    public function test_resource_query_respects_self_data_scope(): void
    {
        $user = $this->user();
        $otherUser = $this->user();
        $tenant = $this->tenant($user);

        $user->forceFill(['is_platform_admin' => false])->save();
        $otherUser->forceFill(['is_platform_admin' => false])->save();
        $tenant->users()->updateExistingPivot($user->id, [
            'is_owner' => false,
            'is_admin' => false,
        ]);
        $tenant->users()->attach($otherUser->id, [
            'member_name' => $otherUser->name,
            'is_owner' => false,
            'is_admin' => false,
            'status' => 'active',
            'joined_at' => now(),
        ]);

        $permission = Permission::create(['name' => 'lead.view_any', 'guard_name' => 'web', 'label' => '线索查看', 'group' => '线索']);
        $role = Role::create(['tenant_id' => $tenant->id, 'name' => '销售', 'guard_name' => 'web', 'data_scope' => 'self']);
        $role->permissions()->attach($permission->id);
        $user->roles()->attach($role->id, ['tenant_id' => $tenant->id, 'model_type' => User::class]);

        Lead::create(['tenant_id' => $tenant->id, 'company_name' => 'Mine', 'owner_user_id' => $user->id]);
        Lead::create(['tenant_id' => $tenant->id, 'company_name' => 'Other', 'owner_user_id' => $otherUser->id]);

        app()->instance('currentTenant', $tenant);
        $this->actingAs($user);

        $this->assertSame(['Mine'], LeadResource::getEloquentQuery()->pluck('company_name')->all());
    }

    public function test_report_metric_pages_have_core_rows(): void
    {
        $user = $this->user();
        $tenant = $this->tenant($user);

        Filament::setTenant($tenant, true);

        try {
            $lead = Lead::create([
                'tenant_id' => $tenant->id,
                'company_name' => '报表线索',
                'source' => '官网',
                'status' => 'converted',
                'converted_at' => now(),
            ]);

            $customer = Customer::create([
                'tenant_id' => $tenant->id,
                'name' => '报表客户',
                'customer_type' => 'company',
                'lifecycle_stage' => 'active',
                'owner_user_id' => $user->id,
            ]);

            $pipeline = Pipeline::create([
                'tenant_id' => $tenant->id,
                'name' => '报表管道',
                'is_default' => true,
                'is_active' => true,
            ]);

            $stage = $pipeline->stages()->create([
                'tenant_id' => $tenant->id,
                'name' => '方案报价',
                'probability' => 60,
                'stage_type' => 'open',
                'sort_order' => 1,
                'is_active' => true,
            ]);

            Opportunity::create([
                'tenant_id' => $tenant->id,
                'customer_id' => $customer->id,
                'pipeline_id' => $pipeline->id,
                'pipeline_stage_id' => $stage->id,
                'name' => '报表商机',
                'amount' => 5000,
                'probability' => 60,
                'forecast_category' => 'commit',
                'expected_close_date' => now()->addWeek(),
                'responsible_user_id' => $user->id,
            ]);

            $order = Order::create([
                'tenant_id' => $tenant->id,
                'order_number' => 'SO-REPORT',
                'customer_id' => $customer->id,
                'employee_id' => $user->id,
                'total_amount' => 3000,
                'gross_profit' => 1200,
                'order_source' => 'sales_entry',
                'order_status' => 'confirmed',
                'payment_status' => 'partial_paid',
                'ordered_at' => now(),
            ]);

            OrderItem::create([
                'tenant_id' => $tenant->id,
                'order_id' => $order->id,
                'product_id' => 1,
                'product_name' => '报表商品',
                'sku_code' => 'REPORT-SKU',
                'quantity' => 2,
                'unit_price' => 1500,
                'cost_price' => 900,
                'subtotal_amount' => 3000,
            ]);

            SalesTarget::create([
                'tenant_id' => $tenant->id,
                'target_type' => 'user',
                'target_id' => $user->id,
                'period_type' => 'month',
                'period_start' => now()->startOfMonth(),
                'period_end' => now()->endOfMonth(),
                'target_amount' => 10000,
                'target_payment_amount' => 5000,
            ]);

            app(CustomerPoolService::class)->releaseCustomer($customer, '超期未跟进');
            app(CustomerPoolService::class)->claimCustomer($customer->refresh(), $user);

            $this->assertNotEmpty(CrmMetrics::pipelineReport()['rows']);
            $this->assertNotEmpty(CrmMetrics::forecastReport()['rows']);
            $this->assertNotEmpty(CrmMetrics::salesTargetReport()['rows']);
            $this->assertNotEmpty(CrmMetrics::leadConversionReport()['rows']);
            $this->assertNotEmpty(CrmMetrics::poolReport()['rows']);
            $this->assertNotEmpty(CrmMetrics::productSalesReport()['rows']);
        } finally {
            Filament::setTenant(null, true);
        }
    }

    public function test_tenant_invitation_accepts_and_assigns_role_and_department(): void
    {
        $admin = $this->user();
        $tenant = $this->tenant($admin);
        $role = Role::create(['tenant_id' => $tenant->id, 'name' => '销售', 'guard_name' => 'web', 'data_scope' => 'self']);
        $department = Department::create(['tenant_id' => $tenant->id, 'name' => '华东一部']);

        $invitation = TenantInvitation::create([
            'tenant_id' => $tenant->id,
            'email' => 'invitee@example.com',
            'role_ids' => [$role->id],
            'department_ids' => [$department->id],
            'invited_by' => $admin->id,
            'expires_at' => now()->addDay(),
        ]);

        $user = app(TenantInvitationService::class)->accept($invitation, [
            'name' => 'Invitee',
            'email' => 'invitee@example.com',
            'password' => 'password123',
        ]);

        $this->assertSame('accepted', $invitation->refresh()->status);
        $this->assertTrue($user->canAccessTenant($tenant));
        $this->assertDatabaseHas('model_has_roles', [
            'role_id' => $role->id,
            'model_id' => $user->id,
            'tenant_id' => $tenant->id,
        ]);
        $this->assertDatabaseHas('department_user', [
            'tenant_id' => $tenant->id,
            'department_id' => $department->id,
            'user_id' => $user->id,
        ]);
    }

    public function test_import_failure_can_be_retried_and_notifies_user(): void
    {
        $user = $this->user();
        $tenant = $this->tenant($user);

        Storage::fake('local');
        Storage::disk('local')->put('imports/leads.csv', "company_name,contact_name,phone\n,,\n");

        $import = app(DataPortService::class)->import($tenant->id, $user, 'leads', 'imports/leads.csv');
        $failed = $import->failedRows()->firstOrFail();

        $failed->forceFill([
            'data' => ['company_name' => '重试线索', 'phone' => '13900009999'],
        ])->save();

        $ok = app(DataPortService::class)->retryFailedRow($failed->refresh(), $user);

        $this->assertTrue($ok);
        $this->assertDatabaseHas('leads', [
            'tenant_id' => $tenant->id,
            'company_name' => '重试线索',
        ]);
        $this->assertDatabaseMissing('failed_import_rows', ['id' => $failed->id]);
        $this->assertDatabaseHas('notifications', [
            'tenant_id' => $tenant->id,
            'notifiable_id' => $user->id,
            'type' => 'import_completed',
        ]);
    }

    public function test_notifications_can_be_marked_read_and_subscription_lifecycle_sends_reminders(): void
    {
        $user = $this->user();
        $tenant = $this->tenant($user);
        $plan = Plan::create([
            'name' => '标准版',
            'code' => 'standard',
            'price_monthly' => 100,
            'price_yearly' => 1000,
            'is_active' => true,
        ]);

        $subscription = TenantSubscription::create([
            'tenant_id' => $tenant->id,
            'plan_id' => $plan->id,
            'status' => 'active',
            'billing_cycle' => 'monthly',
            'starts_at' => now()->subMonth(),
            'ends_at' => now()->addDays(3),
        ]);

        $result = app(SubscriptionLifecycleService::class)->run();
        $notification = CrmNotification::where('tenant_id', $tenant->id)->where('type', 'subscription_expiring')->firstOrFail();

        $this->assertSame(1, $result['reminded']);
        $this->assertNull($notification->read_at);

        app(NotificationService::class)->markRead($notification);
        $this->assertNotNull($notification->refresh()->read_at);

        $subscription->forceFill(['ends_at' => now()->subDay()])->save();
        app(SubscriptionLifecycleService::class)->run();
        $this->assertSame('expired', $subscription->refresh()->status);
    }

    public function test_business_number_rules_generate_numbers_and_field_history_tracks_changes(): void
    {
        $user = $this->user();
        $tenant = $this->tenant($user);
        $this->actingAs($user);

        BusinessNumberRule::create([
            'tenant_id' => $tenant->id,
            'module' => 'customer',
            'name' => '客户编号',
            'prefix' => 'C',
            'pattern' => '{PREFIX}-{YYYY}{MM}{DD}-{SEQ}',
            'sequence_length' => 3,
            'reset_period' => 'daily',
            'is_active' => true,
        ]);

        $customer = Customer::create([
            'tenant_id' => $tenant->id,
            'name' => '编号客户',
            'phone' => '138 0000 0000',
            'owner_user_id' => $user->id,
        ]);

        $this->assertMatchesRegularExpression('/^C-\d{8}-001$/', $customer->refresh()->customer_number);

        $customer->forceFill(['name' => '编号客户新版'])->save();

        $this->assertDatabaseHas('field_histories', [
            'tenant_id' => $tenant->id,
            'model_type' => Customer::class,
            'model_id' => $customer->id,
            'field' => 'name',
        ]);

        $history = FieldHistory::where('model_type', Customer::class)->where('model_id', $customer->id)->where('field', 'name')->firstOrFail();
        $this->assertSame('编号客户', data_get($history->old_value, 'value'));
        $this->assertSame('编号客户新版', data_get($history->new_value, 'value'));
    }

    public function test_plan_feature_gates_automation_import_export_and_storage_usage(): void
    {
        $user = $this->user();
        $tenant = $this->tenant($user);
        $this->actingAs($user);
        $plan = Plan::create([
            'name' => '受限套餐',
            'code' => 'limited',
            'price_monthly' => 99,
            'price_yearly' => 999,
            'max_storage_mb' => 2,
            'max_imports_daily' => 1,
            'features' => [
                'automation' => false,
                'import' => true,
                'export' => false,
            ],
            'is_active' => true,
        ]);

        TenantSubscription::create([
            'tenant_id' => $tenant->id,
            'plan_id' => $plan->id,
            'status' => 'active',
            'billing_cycle' => 'monthly',
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addMonth(),
        ]);

        try {
            Filament::setTenant($tenant);
            Storage::fake('local');
            Storage::disk('local')->put('imports/limited.csv', "company_name,phone\n套餐线索,13911112222\n");

            $this->assertFalse(app(PlanLimitService::class)->hasFeature($tenant->refresh(), 'automation'));
            $this->assertFalse(AutomationRuleResource::canViewAny());

            app(DataPortService::class)->import($tenant->id, $user, 'leads', 'imports/limited.csv');

            $this->expectException(ValidationException::class);
            app(DataPortService::class)->import($tenant->id, $user, 'leads', 'imports/limited.csv');
        } finally {
            Filament::setTenant(null, true);
        }
    }

    public function test_export_gate_and_platform_overview_surface_usage_warnings(): void
    {
        $user = $this->user();
        $tenant = $this->tenant($user);
        $plan = Plan::create([
            'name' => '平台套餐',
            'code' => 'platform-plan',
            'price_monthly' => 199,
            'price_yearly' => 1999,
            'max_storage_mb' => 2,
            'features' => [
                'export' => false,
            ],
            'is_active' => true,
        ]);

        TenantSubscription::create([
            'tenant_id' => $tenant->id,
            'plan_id' => $plan->id,
            'status' => 'active',
            'billing_cycle' => 'monthly',
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addDays(3),
        ]);

        Attachment::create([
            'tenant_id' => $tenant->id,
            'path' => 'tenants/'.$tenant->id.'/attachments/contract.pdf',
            'disk' => 'local',
            'user_id' => $user->id,
            'model_type' => Customer::class,
            'model_id' => 1,
            'name' => '合同',
            'size' => 2 * 1024 * 1024,
        ]);

        $overview = CrmMetrics::platformOverview();
        $this->assertTrue(collect($overview['rows'])->contains(fn (array $row): bool => str_contains($row['title'], '用量异常')));
        $this->assertTrue(collect($overview['rows'])->contains(fn (array $row): bool => str_contains($row['title'], '即将到期')));

        $this->expectException(ValidationException::class);
        app(DataPortService::class)->export($tenant->id, $user, 'customers');
    }

    private function user(): User
    {
        return User::create([
            'name' => 'Tester',
            'email' => fake()->unique()->safeEmail(),
            'password' => Hash::make('password'),
            'status' => true,
            'is_platform_admin' => true,
        ]);
    }

    private function tenant(User $user): Tenant
    {
        $tenant = Tenant::create([
            'name' => '测试租户',
            'slug' => 'tenant-'.Str::random(8),
            'status' => 'active',
        ]);

        $tenant->users()->attach($user->id, [
            'member_name' => $user->name,
            'is_owner' => true,
            'is_admin' => true,
            'status' => 'active',
            'joined_at' => now(),
        ]);

        return $tenant;
    }

    private function attachTenantUser(Tenant $tenant, User $user): void
    {
        $tenant->users()->attach($user->id, [
            'member_name' => $user->name,
            'is_owner' => false,
            'is_admin' => false,
            'status' => 'active',
            'joined_at' => now(),
        ]);
    }
}

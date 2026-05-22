<?php

namespace Tests\Feature;

use App\Filament\Clusters\LeadCenter\Resources\Leads\LeadResource;
use App\Models\CustomField;
use App\Models\AutomationAction;
use App\Models\AutomationRule;
use App\Models\Contact;
use App\Models\Customer;
use App\Models\Lead;
use App\Models\Opportunity;
use App\Models\Order;
use App\Models\OrderPaymentPlan;
use App\Models\Payment;
use App\Models\Permission;
use App\Models\Pipeline;
use App\Models\PriceBook;
use App\Models\PriceBookItem;
use App\Models\Product;
use App\Models\ProductGroup;
use App\Models\ProductSku;
use App\Models\Quote;
use App\Models\QuoteItem;
use App\Models\Role;
use App\Models\Setting;
use App\Models\Tenant;
use App\Models\User;
use App\Services\Crm\CustomerMergeService;
use App\Services\Crm\CustomerPoolService;
use App\Services\Crm\DataPortService;
use App\Services\Crm\LeadConversionService;
use App\Services\Crm\OpportunityStageService;
use App\Services\Crm\QuotePdfService;
use App\Services\Crm\QuoteToOrderService;
use App\Support\Filament\CustomFieldUi;
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
}

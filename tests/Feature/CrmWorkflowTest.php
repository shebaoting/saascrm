<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Lead;
use App\Models\Pipeline;
use App\Models\Product;
use App\Models\ProductGroup;
use App\Models\ProductSku;
use App\Models\Quote;
use App\Models\QuoteItem;
use App\Models\Tenant;
use App\Models\User;
use App\Services\Crm\LeadConversionService;
use App\Services\Crm\QuotePdfService;
use App\Services\Crm\QuoteToOrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
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

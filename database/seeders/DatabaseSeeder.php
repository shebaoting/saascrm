<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Pipeline;
use App\Models\Plan;
use App\Models\PriceBook;
use App\Models\Product;
use App\Models\ProductGroup;
use App\Models\ProductSku;
use App\Models\Role;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $user = User::query()->updateOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                'phone' => '13800000000',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'locale' => 'zh_CN',
                'status' => true,
                'is_platform_admin' => true,
            ],
        );

        $plan = Plan::query()->updateOrCreate(
            ['code' => 'starter'],
            [
                'name' => 'Starter',
                'price_monthly' => 0,
                'price_yearly' => 0,
                'max_users' => 20,
                'max_leads' => 5000,
                'max_customers' => 2000,
                'max_storage_mb' => 2048,
                'max_custom_fields' => 50,
                'max_automation_rules' => 20,
                'features' => ['crm' => true, 'quotes' => true, 'reports' => true],
                'is_active' => true,
            ],
        );

        $tenant = Tenant::query()->updateOrCreate(
            ['slug' => 'demo-crm'],
            [
                'name' => 'Demo CRM',
                'short_name' => 'Demo',
                'status' => 'active',
                'trial_ends_at' => now()->addMonth(),
                'contact_name' => $user->name,
                'contact_email' => $user->email,
            ],
        );

        $tenant->users()->syncWithoutDetaching([
            $user->id => [
                'member_name' => $user->name,
                'is_owner' => true,
                'is_admin' => true,
                'status' => 'active',
                'joined_at' => now(),
            ],
        ]);

        $tenant->subscriptions()->updateOrCreate(
            ['plan_id' => $plan->id],
            [
                'status' => 'active',
                'billing_cycle' => 'manual',
                'starts_at' => now()->subDay(),
                'ends_at' => now()->addYear(),
            ],
        );

        $permissions = collect([
            'lead.view_any' => ['线索查看', '线索'],
            'lead.create' => ['线索创建', '线索'],
            'lead.update' => ['线索更新', '线索'],
            'lead.claim' => ['线索领取', '线索'],
            'lead.convert' => ['线索转客户', '线索'],
            'customer.view_any' => ['客户查看', '客户'],
            'customer.create' => ['客户创建', '客户'],
            'customer.update' => ['客户更新', '客户'],
            'activity.create' => ['活动创建', '活动'],
            'task.complete' => ['任务完成', '任务'],
            'opportunity.update' => ['商机推进', '商机'],
            'quote.approve' => ['报价审批', '报价'],
            'quote.convert_order' => ['报价转订单', '报价'],
            'order.update' => ['订单更新', '订单'],
            'payment.update' => ['收款更新', '财务'],
            'report.sales' => ['销售报表', '报表'],
            'report.finance' => ['财务报表', '报表'],
            'settings.manage' => ['系统设置', '设置'],
        ])->map(function (array $meta, string $name): Permission {
            return Permission::query()->updateOrCreate(
                ['name' => $name, 'guard_name' => 'web'],
                ['label' => $meta[0], 'group' => $meta[1]],
            );
        });

        $role = Role::query()->updateOrCreate(
            ['tenant_id' => $tenant->id, 'name' => '租户管理员', 'guard_name' => 'web'],
            ['data_scope' => 'all'],
        );

        $role->permissions()->sync($permissions->pluck('id')->all());
        $user->roles()->syncWithoutDetaching([
            $role->id => [
                'tenant_id' => $tenant->id,
                'model_type' => User::class,
            ],
        ]);

        $pipeline = Pipeline::query()->updateOrCreate(
            ['tenant_id' => $tenant->id, 'name' => '默认销售管道'],
            ['description' => '从初步沟通到赢单的标准销售流程', 'is_default' => true, 'is_active' => true, 'sort_order' => 1],
        );

        foreach ([
            ['初步沟通', 10, 'open'],
            ['需求确认', 30, 'open'],
            ['方案报价', 60, 'open'],
            ['商务谈判', 80, 'open'],
            ['赢单', 100, 'won'],
            ['输单', 0, 'lost'],
        ] as $index => [$name, $probability, $type]) {
            $pipeline->stages()->updateOrCreate(
                ['tenant_id' => $tenant->id, 'name' => $name],
                ['probability' => $probability, 'stage_type' => $type, 'sort_order' => $index + 1, 'is_active' => true],
            );
        }

        $group = ProductGroup::query()->updateOrCreate(
            ['tenant_id' => $tenant->id, 'name' => '标准服务'],
            ['sort_order' => 1],
        );

        $product = Product::query()->updateOrCreate(
            ['tenant_id' => $tenant->id, 'name' => 'CRM 实施服务'],
            ['group_id' => $group->id, 'tax_rate' => 6, 'is_on_sale' => true],
        );

        $sku = ProductSku::query()->updateOrCreate(
            ['tenant_id' => $tenant->id, 'sku_code' => 'CRM-SETUP'],
            ['product_id' => $product->id, 'price' => 9800, 'cost_price' => 3000, 'stock' => 999, 'is_active' => true],
        );

        $priceBook = PriceBook::query()->updateOrCreate(
            ['tenant_id' => $tenant->id, 'code' => 'standard'],
            ['name' => '标准价目表', 'currency' => 'CNY', 'is_default' => true, 'is_active' => true],
        );

        $priceBook->items()->updateOrCreate(
            ['tenant_id' => $tenant->id, 'product_sku_id' => $sku->id],
            ['price' => 9800, 'min_price' => 7800],
        );
    }
}

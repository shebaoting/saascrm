<?php

namespace App\Filament\Pages\Tenancy;

use App\Models\Plan;
use App\Models\Tenant;
use Filament\Forms\Components\TextInput;
use Filament\Pages\Tenancy\RegisterTenant as BaseRegisterTenant;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class RegisterTenant extends BaseRegisterTenant
{
    public static function getLabel(): string
    {
        return '创建公司';
    }

    public static function canView(): bool
    {
        return Auth::check();
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')
                ->label('公司名称')
                ->required()
                ->live(onBlur: true)
                ->afterStateUpdated(fn ($state, callable $set) => $set('slug', Str::slug((string) $state)))
                ->maxLength(255),
            TextInput::make('slug')
                ->label('访问标识')
                ->required()
                ->alphaDash()
                ->unique(Tenant::class, 'slug')
                ->maxLength(100),
            TextInput::make('contact_name')
                ->label('联系人')
                ->maxLength(100),
            TextInput::make('contact_phone')
                ->label('联系电话')
                ->tel()
                ->maxLength(50),
            TextInput::make('contact_email')
                ->label('联系邮箱')
                ->email()
                ->maxLength(255),
        ]);
    }

    protected function handleRegistration(array $data): Model
    {
        $tenant = Tenant::create([
            ...$data,
            'status' => 'trial',
            'trial_ends_at' => now()->addDays(14),
        ]);

        $tenant->users()->attach(Auth::id(), [
            'member_name' => Auth::user()?->name,
            'is_owner' => true,
            'is_admin' => true,
            'status' => 'active',
            'joined_at' => now(),
        ]);

        $plan = Plan::query()->firstOrCreate(
            ['code' => 'starter'],
            [
                'name' => 'Starter',
                'price_monthly' => 0,
                'price_yearly' => 0,
                'max_users' => 10,
                'max_leads' => 1000,
                'max_customers' => 500,
                'max_storage_mb' => 1024,
                'max_custom_fields' => 20,
                'max_automation_rules' => 10,
                'features' => [
                    'crm' => true,
                    'quotes' => true,
                    'orders' => true,
                    'reports' => true,
                    'automation' => true,
                    'import' => true,
                    'export' => true,
                    'pdf' => true,
                    'custom_fields' => true,
                ],
                'is_active' => true,
            ],
        );

        $tenant->subscriptions()->create([
            'plan_id' => $plan->id,
            'status' => 'trialing',
            'billing_cycle' => 'manual',
            'starts_at' => now(),
            'ends_at' => now()->addDays(14),
        ]);

        return $tenant;
    }
}

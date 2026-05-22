<?php

namespace App\Services\Crm;

use App\Models\AutomationRule;
use App\Models\Customer;
use App\Models\CustomField;
use App\Models\Lead;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

class PlanLimitService
{
    public function canCreate(string $modelClass, ?Tenant $tenant): bool
    {
        if (! $tenant || ! $tenant->activeSubscription?->plan) {
            return true;
        }

        [$limitColumn, $current] = $this->usage($modelClass, $tenant) ?? [null, null];

        if (! $limitColumn) {
            return true;
        }

        $limit = $tenant->activeSubscription->plan->{$limitColumn};

        return blank($limit) || $current < $limit;
    }

    public function assertCanCreate(Model|string $model, ?Tenant $tenant): void
    {
        if (! $tenant) {
            return;
        }

        $modelClass = is_string($model) ? $model : $model::class;

        if ($this->canCreate($modelClass, $tenant)) {
            return;
        }

        throw ValidationException::withMessages([
            'plan' => '当前套餐用量已达上限，请升级套餐后继续添加。',
        ]);
    }

    /**
     * @return array{0: string, 1: int}|null
     */
    private function usage(string $modelClass, Tenant $tenant): ?array
    {
        return match ($modelClass) {
            User::class => ['max_users', $tenant->users()->count()],
            Lead::class => ['max_leads', Lead::where('tenant_id', $tenant->id)->count()],
            Customer::class => ['max_customers', Customer::where('tenant_id', $tenant->id)->count()],
            CustomField::class => ['max_custom_fields', CustomField::where('tenant_id', $tenant->id)->count()],
            AutomationRule::class => ['max_automation_rules', AutomationRule::where('tenant_id', $tenant->id)->count()],
            default => null,
        };
    }
}

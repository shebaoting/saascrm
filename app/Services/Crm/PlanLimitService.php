<?php

namespace App\Services\Crm;

use App\Models\AutomationRule;
use App\Models\Attachment;
use App\Models\Customer;
use App\Models\CustomField;
use App\Models\Export;
use App\Models\Import;
use App\Models\Lead;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

class PlanLimitService
{
    /**
     * @return array<string, string>
     */
    public static function featureLabels(): array
    {
        return [
            'crm' => 'CRM 基础功能',
            'quotes' => '报价管理',
            'orders' => '订单与财务',
            'reports' => '经营报表',
            'automation' => '自动化规则',
            'import' => '数据导入',
            'export' => '数据导出',
            'pdf' => 'PDF 生成',
            'custom_fields' => '自定义字段',
        ];
    }

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

    public function hasFeature(?Tenant $tenant, string $feature): bool
    {
        if (! $tenant || ! $tenant->activeSubscription?->plan) {
            return true;
        }

        $features = $tenant->activeSubscription->plan->features ?: [];

        return ! array_key_exists($feature, $features) || (bool) $features[$feature];
    }

    public function assertFeature(?Tenant $tenant, string $feature): void
    {
        if ($this->hasFeature($tenant, $feature)) {
            return;
        }

        $label = self::featureLabels()[$feature] ?? $feature;

        throw ValidationException::withMessages([
            'plan' => "当前套餐不包含「{$label}」，请升级套餐后继续使用。",
        ]);
    }

    public function assertCanImport(Tenant $tenant): void
    {
        $this->assertFeature($tenant, 'import');

        $limit = $tenant->activeSubscription?->plan?->max_imports_daily;

        if (blank($limit)) {
            return;
        }

        $used = Import::query()
            ->where('tenant_id', $tenant->id)
            ->whereDate('created_at', now()->toDateString())
            ->count();

        if ($used >= (int) $limit) {
            throw ValidationException::withMessages([
                'plan' => '今天的导入次数已达套餐上限，请明天再试或升级套餐。',
            ]);
        }
    }

    public function assertCanExport(Tenant $tenant): void
    {
        $this->assertFeature($tenant, 'export');

        $limit = $tenant->activeSubscription?->plan?->max_exports_daily;

        if (blank($limit)) {
            return;
        }

        $used = Export::query()
            ->where('tenant_id', $tenant->id)
            ->whereDate('created_at', now()->toDateString())
            ->count();

        if ($used >= (int) $limit) {
            throw ValidationException::withMessages([
                'plan' => '今天的导出次数已达套餐上限，请明天再试或升级套餐。',
            ]);
        }
    }

    /**
     * @return array{used_mb: float, limit_mb: int|null, percent: float|null, exceeded: bool}
     */
    public function storageStatus(Tenant $tenant): array
    {
        $usedMb = round((float) Attachment::query()
            ->where('tenant_id', $tenant->id)
            ->sum('size') / 1024 / 1024, 2);
        $limitMb = $tenant->activeSubscription?->plan?->max_storage_mb;
        $percent = blank($limitMb) ? null : round($usedMb / max(1, (int) $limitMb) * 100, 2);

        return [
            'used_mb' => $usedMb,
            'limit_mb' => $limitMb === null ? null : (int) $limitMb,
            'percent' => $percent,
            'exceeded' => $percent !== null && $percent >= 100,
        ];
    }

    public function assertStorageAvailable(Tenant $tenant, int $additionalBytes = 0): void
    {
        $limit = $tenant->activeSubscription?->plan?->max_storage_mb;

        if (blank($limit)) {
            return;
        }

        $usedBytes = (int) Attachment::query()
            ->where('tenant_id', $tenant->id)
            ->sum('size');

        if (($usedBytes + $additionalBytes) <= ((int) $limit) * 1024 * 1024) {
            return;
        }

        throw ValidationException::withMessages([
            'plan' => '当前附件存储容量已达套餐上限，请清理文件或升级套餐。',
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

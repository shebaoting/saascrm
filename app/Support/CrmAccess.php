<?php

namespace App\Support;

use App\Models\Activity;
use App\Models\Attachment;
use App\Models\AutomationAction;
use App\Models\AutomationRule;
use App\Models\BusinessNumberRule;
use App\Models\Contact;
use App\Models\Customer;
use App\Models\CustomerPoolHistory;
use App\Models\CustomerPoolRule;
use App\Models\CustomerTransferHistory;
use App\Models\CustomField;
use App\Models\CustomFieldLayout;
use App\Models\Department;
use App\Models\DuplicateRecord;
use App\Models\Export;
use App\Models\FailedImportRow;
use App\Models\Import;
use App\Models\KbArticle;
use App\Models\KbCategory;
use App\Models\Lead;
use App\Models\LeadScoreRule;
use App\Models\MergeHistory;
use App\Models\Opportunity;
use App\Models\OpportunityStageHistory;
use App\Models\Order;
use App\Models\OrderExpense;
use App\Models\OrderItem;
use App\Models\OrderPaymentPlan;
use App\Models\Payment;
use App\Models\Pipeline;
use App\Models\PipelineStage;
use App\Models\PriceBook;
use App\Models\PriceBookItem;
use App\Models\Product;
use App\Models\ProductGroup;
use App\Models\ProductSku;
use App\Models\Quote;
use App\Models\QuoteApprovalRequest;
use App\Models\QuoteItem;
use App\Models\Role;
use App\Models\SalesTarget;
use App\Models\Setting;
use App\Models\Task;
use App\Models\Tenant;
use App\Models\TenantInvitation;
use App\Models\User;
use App\Services\Crm\PlanLimitService;
use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CrmAccess
{
    /**
     * @var array<string, bool>
     */
    private static array $tableColumnCache = [];

    public static function tenant(): ?Tenant
    {
        $tenant = Filament::getTenant();

        if ($tenant instanceof Tenant) {
            return $tenant;
        }

        return app()->bound('currentTenant') ? app('currentTenant') : null;
    }

    public static function tenantId(): ?int
    {
        return self::tenant()?->getKey();
    }

    public static function user(): ?User
    {
        $user = Auth::user();

        return $user instanceof User ? $user : null;
    }

    public static function isTenantAdmin(?User $user = null, ?int $tenantId = null): bool
    {
        $user ??= self::user();
        $tenantId ??= self::tenantId();

        if (! $user) {
            return false;
        }

        if ($user->is_platform_admin) {
            return true;
        }

        if (! $tenantId) {
            return false;
        }

        return $user->tenants()
            ->whereKey($tenantId)
            ->wherePivot('status', 'active')
            ->where(function ($query): void {
                $query->where('tenant_user.is_owner', true)
                    ->orWhere('tenant_user.is_admin', true);
            })
            ->exists();
    }

    public static function hasPermission(string $permission): bool
    {
        $user = self::user();
        $tenantId = self::tenantId();

        if (! $user) {
            return false;
        }

        if (! $tenantId) {
            return true;
        }

        if (self::isTenantAdmin($user, $tenantId)) {
            return true;
        }

        $permissionExists = DB::table('permissions')->where('name', $permission)->exists();

        if (! $permissionExists) {
            return $user->canAccessTenant(self::tenant());
        }

        return $user->roles()
            ->wherePivot('tenant_id', $tenantId)
            ->whereHas('permissions', fn ($query) => $query->where('name', $permission))
            ->exists();
    }

    public static function canForModel(string $modelClass, string $ability): bool
    {
        if (self::permissionAbility($ability) === 'create' && ! app(PlanLimitService::class)->canCreate($modelClass, self::tenant())) {
            return false;
        }

        $prefix = self::permissionPrefix($modelClass);
        $permission = $prefix.'.'.self::permissionAbility($ability);

        return self::hasPermission($permission);
    }

    public static function canForRecord(Model $record, string $ability): bool
    {
        if (! self::canForModel($record::class, $ability)) {
            return false;
        }

        return self::recordIsVisible($record);
    }

    public static function recordIsVisible(Model $record): bool
    {
        $tenantId = self::tenantId();

        if (! $tenantId || self::isTenantAdmin()) {
            return true;
        }

        if (self::dataScope() === 'all') {
            return true;
        }

        return self::scopeQuery($record::query()->whereKey($record->getKey()), $record::class)->exists();
    }

    public static function scopeQuery(Builder $query, string $modelClass): Builder
    {
        $tenantId = self::tenantId();

        if ($tenantId && self::hasColumn($query->getModel()->getTable(), 'tenant_id')) {
            $query->where($query->getModel()->getTable().'.tenant_id', $tenantId);
        }

        if (! $tenantId || self::isTenantAdmin()) {
            return $query;
        }

        $scope = self::dataScope();

        if ($scope === 'all' || ! self::modelNeedsDataScope($modelClass)) {
            return $query;
        }

        $userIds = self::visibleUserIds($scope);

        if ($userIds === []) {
            return $query->whereRaw('1 = 0');
        }

        return self::applyOwnerScope($query, $modelClass, $userIds);
    }

    public static function dataScope(): string
    {
        $user = self::user();
        $tenantId = self::tenantId();

        if (! $user || ! $tenantId) {
            return 'all';
        }

        $scopes = $user->roles()
            ->wherePivot('tenant_id', $tenantId)
            ->pluck('data_scope')
            ->filter()
            ->all();

        if ($scopes === []) {
            return 'self';
        }

        $rank = [
            'self' => 10,
            'department' => 20,
            'department_tree' => 30,
            'custom' => 30,
            'all' => 40,
        ];

        usort($scopes, fn (string $left, string $right): int => ($rank[$right] ?? 0) <=> ($rank[$left] ?? 0));

        return $scopes[0];
    }

    /**
     * @return array<int>
     */
    public static function visibleUserIds(?string $scope = null): array
    {
        $user = self::user();
        $tenantId = self::tenantId();

        if (! $user || ! $tenantId) {
            return [];
        }

        $scope ??= self::dataScope();

        if ($scope === 'self') {
            return [$user->id];
        }

        if ($scope === 'custom') {
            return self::customVisibleUserIds($user, $tenantId);
        }

        $departmentIds = DB::table('department_user')
            ->where('tenant_id', $tenantId)
            ->where('user_id', $user->id)
            ->pluck('department_id')
            ->all();

        if ($scope === 'department_tree') {
            $departmentIds = self::departmentTreeIds($departmentIds, $tenantId);
        }

        if ($departmentIds === []) {
            return [$user->id];
        }

        $userIds = DB::table('department_user')
            ->where('tenant_id', $tenantId)
            ->whereIn('department_id', $departmentIds)
            ->pluck('user_id')
            ->all();

        return array_values(array_unique([...$userIds, $user->id]));
    }

    /**
     * @return array<int>
     */
    private static function customVisibleUserIds(User $user, int $tenantId): array
    {
        $roles = $user->roles()
            ->wherePivot('tenant_id', $tenantId)
            ->where('data_scope', 'custom')
            ->get(['roles.id', 'custom_department_ids', 'custom_user_ids']);

        $departmentIds = $roles
            ->flatMap(fn (Role $role): array => $role->custom_department_ids ?: [])
            ->filter()
            ->map(fn (mixed $id): int => (int) $id)
            ->unique()
            ->values()
            ->all();

        $userIds = $roles
            ->flatMap(fn (Role $role): array => $role->custom_user_ids ?: [])
            ->filter()
            ->map(fn (mixed $id): int => (int) $id)
            ->unique()
            ->values()
            ->all();

        if ($departmentIds !== []) {
            $departmentUserIds = DB::table('department_user')
                ->where('tenant_id', $tenantId)
                ->whereIn('department_id', $departmentIds)
                ->pluck('user_id')
                ->all();

            $userIds = [...$userIds, ...$departmentUserIds];
        }

        $userIds = array_values(array_unique(array_map('intval', $userIds)));

        return $userIds === [] ? [$user->id] : $userIds;
    }

    /**
     * @param  array<int>  $departmentIds
     * @return array<int>
     */
    private static function departmentTreeIds(array $departmentIds, int $tenantId): array
    {
        $allIds = array_values(array_unique($departmentIds));
        $frontier = $allIds;

        while ($frontier !== []) {
            $children = Department::query()
                ->where('tenant_id', $tenantId)
                ->whereIn('parent_id', $frontier)
                ->pluck('id')
                ->all();

            $children = array_values(array_diff($children, $allIds));

            if ($children === []) {
                break;
            }

            $allIds = array_values(array_unique([...$allIds, ...$children]));
            $frontier = $children;
        }

        return $allIds;
    }

    /**
     * @param  array<int>  $userIds
     */
    private static function applyOwnerScope(Builder $query, string $modelClass, array $userIds): Builder
    {
        return match ($modelClass) {
            Lead::class => self::scopeByColumns($query, ['owner_user_id', 'created_by'], $userIds),
            Customer::class => $query->where(function (Builder $query) use ($userIds): void {
                self::scopeByColumns($query, ['owner_user_id', 'created_by'], $userIds);
                $query->orWhereHas('members', fn (Builder $query) => $query->whereIn('users.id', $userIds));
            }),
            Contact::class => $query->whereHas('customer', fn (Builder $query) => self::scopeByColumns($query, ['owner_user_id'], $userIds)),
            Activity::class => self::scopeByColumns($query, ['owner_user_id', 'created_by'], $userIds),
            Task::class => self::scopeByColumns($query, ['assignee_id', 'creator_id', 'created_by'], $userIds),
            Opportunity::class => self::scopeByColumns($query, ['responsible_user_id', 'created_by'], $userIds),
            Quote::class => self::scopeByColumns($query, ['user_id', 'created_by'], $userIds),
            QuoteApprovalRequest::class => self::scopeByColumns($query, ['requested_by', 'approver_id'], $userIds),
            Order::class => self::scopeByColumns($query, ['employee_id', 'created_by'], $userIds),
            OrderItem::class => $query->whereHas('order', fn (Builder $query) => self::scopeByColumns($query, ['employee_id', 'created_by'], $userIds)),
            OrderPaymentPlan::class => $query->whereHas('order', fn (Builder $query) => self::scopeByColumns($query, ['employee_id', 'created_by'], $userIds)),
            Payment::class => $query->whereHas('order', fn (Builder $query) => self::scopeByColumns($query, ['employee_id', 'created_by'], $userIds)),
            OrderExpense::class => $query->whereHas('order', fn (Builder $query) => self::scopeByColumns($query, ['employee_id', 'created_by'], $userIds)),
            default => self::hasColumn($query->getModel()->getTable(), 'created_by')
                ? self::scopeByColumns($query, ['created_by'], $userIds)
                : $query,
        };
    }

    /**
     * @param  array<string>  $columns
     * @param  array<int>  $userIds
     */
    private static function scopeByColumns(Builder $query, array $columns, array $userIds): Builder
    {
        $table = $query->getModel()->getTable();
        $validColumns = array_values(array_filter($columns, fn (string $column): bool => self::hasColumn($table, $column)));

        if ($validColumns === []) {
            return $query;
        }

        return $query->where(function (Builder $query) use ($table, $validColumns, $userIds): void {
            foreach ($validColumns as $index => $column) {
                $method = $index === 0 ? 'whereIn' : 'orWhereIn';
                $query->{$method}($table.'.'.$column, $userIds);
            }
        });
    }

    private static function hasColumn(string $table, string $column): bool
    {
        $key = $table.'.'.$column;

        return self::$tableColumnCache[$key] ??= Schema::hasColumn($table, $column);
    }

    private static function modelNeedsDataScope(string $modelClass): bool
    {
        return in_array($modelClass, [
            Lead::class,
            Customer::class,
            Contact::class,
            Activity::class,
            Task::class,
            Opportunity::class,
            Quote::class,
            QuoteApprovalRequest::class,
            Order::class,
            OrderItem::class,
            OrderPaymentPlan::class,
            Payment::class,
            OrderExpense::class,
        ], true);
    }

    private static function permissionAbility(string $ability): string
    {
        return match ($ability) {
            'viewAny', 'view', 'access' => 'view_any',
            'create' => 'create',
            'update', 'edit' => 'update',
            'delete', 'deleteAny', 'forceDelete', 'forceDeleteAny' => 'delete',
            'restore', 'restoreAny' => 'update',
            default => $ability,
        };
    }

    private static function permissionPrefix(string $modelClass): string
    {
        return match ($modelClass) {
            Lead::class, LeadScoreRule::class => 'lead',
            Customer::class, Contact::class, CustomerPoolRule::class, CustomerPoolHistory::class, CustomerTransferHistory::class, MergeHistory::class => 'customer',
            Activity::class => 'activity',
            Task::class => 'task',
            Opportunity::class, OpportunityStageHistory::class, Pipeline::class, PipelineStage::class => 'opportunity',
            SalesTarget::class => 'report',
            Quote::class, QuoteItem::class, QuoteApprovalRequest::class => 'quote',
            Order::class, OrderItem::class, OrderPaymentPlan::class => 'order',
            Payment::class, OrderExpense::class => 'payment',
            ProductGroup::class, Product::class, ProductSku::class, PriceBook::class, PriceBookItem::class => 'product',
            KbCategory::class, KbArticle::class => 'knowledge',
            Setting::class, Department::class, Role::class, TenantInvitation::class, CustomField::class, CustomFieldLayout::class, BusinessNumberRule::class, DuplicateRecord::class, Import::class, Export::class, FailedImportRow::class, Attachment::class, AutomationRule::class, AutomationAction::class => 'settings',
            User::class => 'settings',
            default => str(class_basename($modelClass))->snake()->toString(),
        };
    }
}

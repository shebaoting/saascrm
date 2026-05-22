<?php

namespace App\Services\Crm;

use App\Models\AssignmentRule;
use App\Models\AssignmentRuleCondition;
use App\Models\CustomerPoolHistory;
use App\Models\Lead;
use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class LeadAssignmentService
{
    public function claim(Lead $lead, User $user): Lead
    {
        $fromUserId = $lead->owner_user_id;

        app(CustomerPoolService::class)->claimLead($lead, $user);

        $lead->forceFill([
            'owner_user_id' => $user->id,
            'status' => 'working',
            'pool_entered_at' => null,
        ])->save();

        app(AuditLogService::class)->record('lead_claimed', $lead, [
            'owner_user_id' => $fromUserId,
        ], [
            'owner_user_id' => $user->id,
        ]);

        return $lead->refresh();
    }

    public function release(Lead $lead, ?string $reason = null): Lead
    {
        $fromUserId = $lead->owner_user_id;

        app(CustomerPoolService::class)->releaseLead($lead, $reason);

        $lead->forceFill([
            'owner_user_id' => null,
            'status' => 'pooled',
            'pool_entered_at' => now(),
            'lost_reason' => $reason,
        ])->save();

        app(AuditLogService::class)->record('lead_released', $lead, [
            'owner_user_id' => $fromUserId,
        ], [
            'owner_user_id' => null,
            'reason' => $reason,
        ]);

        return $lead->refresh();
    }

    public function assignByRules(Lead $lead): ?Lead
    {
        $rule = $this->matchingRule($lead);

        if (! $rule) {
            return null;
        }

        $user = $this->selectUser($lead, $rule);

        if (! $user && Auth::id()) {
            $user = User::query()
                ->whereKey(Auth::id())
                ->whereHas('tenants', fn ($query) => $query->whereKey($lead->tenant_id))
                ->first();
        }

        if (! $user) {
            return null;
        }

        $assigned = $this->claim($lead, $user);

        app(AuditLogService::class)->record('lead_auto_assigned', $assigned, [
            'owner_user_id' => null,
            'assignment_rule_id' => $rule->id,
        ], [
            'owner_user_id' => $user->id,
            'assignment_rule_id' => $rule->id,
            'method' => $rule->method,
        ]);

        return $assigned;
    }

    private function matchingRule(Lead $lead): ?AssignmentRule
    {
        return AssignmentRule::query()
            ->where('tenant_id', $lead->tenant_id)
            ->where('target_type', 'lead')
            ->where('is_active', true)
            ->with('conditions')
            ->orderByDesc('priority')
            ->get()
            ->first(fn (AssignmentRule $rule): bool => $rule->conditions->every(
                fn (AssignmentRuleCondition $condition): bool => $this->conditionMatches($lead, $condition),
            ));
    }

    private function selectUser(Lead $lead, AssignmentRule $rule): ?User
    {
        $candidates = $this->candidateUsers($lead, $rule);

        if ($rule->max_per_user_daily) {
            $candidates = $candidates
                ->filter(fn (User $user): bool => $this->assignedToday($lead->tenant_id, $user) < (int) $rule->max_per_user_daily)
                ->values();
        }

        if ($candidates->isEmpty()) {
            return null;
        }

        if ($rule->method === 'least_busy') {
            return $candidates
                ->sortBy(fn (User $user): int => Lead::query()
                    ->where('tenant_id', $lead->tenant_id)
                    ->where('owner_user_id', $user->id)
                    ->whereNotIn('status', ['converted', 'invalid', 'pooled'])
                    ->count())
                ->first();
        }

        if ($rule->method === 'round_robin') {
            $ordered = $candidates->sortBy('id')->values();
            $lastIndex = $ordered->search(fn (User $user): bool => $user->id === $rule->round_robin_cursor);
            $nextIndex = $lastIndex === false ? 0 : ($lastIndex + 1) % $ordered->count();
            $selected = $ordered[$nextIndex];

            $rule->forceFill(['round_robin_cursor' => $selected->id])->save();

            return $selected;
        }

        return $candidates->first();
    }

    private function candidateUsers(Lead $lead, AssignmentRule $rule)
    {
        $userIds = collect(Arr::wrap($rule->user_ids))->filter()->map(fn ($id): int => (int) $id);

        if ($rule->department_id) {
            $departmentUserIds = DB::table('department_user')
                ->where('tenant_id', $lead->tenant_id)
                ->where('department_id', $rule->department_id)
                ->pluck('user_id')
                ->map(fn ($id): int => (int) $id);

            $userIds = $userIds->merge($departmentUserIds);
        }

        return User::query()
            ->whereIn('id', $userIds->unique()->values())
            ->where('status', true)
            ->whereHas('tenants', fn ($query) => $query->whereKey($lead->tenant_id)->where('tenant_user.status', 'active'))
            ->orderBy('id')
            ->get();
    }

    private function assignedToday(int $tenantId, User $user): int
    {
        return CustomerPoolHistory::query()
            ->where('tenant_id', $tenantId)
            ->where('target_type', Lead::class)
            ->where('action', 'claim')
            ->where('to_user_id', $user->id)
            ->whereDate('created_at', today())
            ->count();
    }

    private function conditionMatches(Lead $lead, AssignmentRuleCondition $condition): bool
    {
        $actual = $this->fieldValue($lead, $condition->field);
        $expected = $this->expectedValues($condition->value);
        $firstExpected = Arr::first($expected);

        return match ($condition->operator) {
            'eq' => (string) $actual === (string) $firstExpected,
            'neq' => (string) $actual !== (string) $firstExpected,
            'contains' => str_contains((string) $actual, (string) $firstExpected),
            'in' => in_array((string) $actual, array_map('strval', $expected), true),
            'not_in' => ! in_array((string) $actual, array_map('strval', $expected), true),
            'not_empty' => filled($actual),
            'empty' => blank($actual),
            'gt' => (float) $actual > (float) $firstExpected,
            'gte' => (float) $actual >= (float) $firstExpected,
            'lt' => (float) $actual < (float) $firstExpected,
            'lte' => (float) $actual <= (float) $firstExpected,
            'between' => count($expected) >= 2
                && (float) $actual >= (float) $expected[0]
                && (float) $actual <= (float) $expected[1],
            default => false,
        };
    }

    private function fieldValue(Lead $lead, string $field): mixed
    {
        return match ($field) {
            'industry' => data_get($lead->custom_fields ?: [], 'industry'),
            default => Str::startsWith($field, 'custom_fields.')
                ? data_get($lead->custom_fields ?: [], Str::after($field, 'custom_fields.'))
                : data_get($lead->getAttributes(), $field),
        };
    }

    private function expectedValues(mixed $value): array
    {
        if (is_string($value)) {
            $decoded = json_decode($value, true);

            if (json_last_error() === JSON_ERROR_NONE) {
                return Arr::wrap($decoded);
            }

            return str_contains($value, ',')
                ? array_map(fn (string $item): string => trim($item), explode(',', $value))
                : [$value];
        }

        return Arr::wrap($value);
    }
}

<?php

namespace App\Services\Crm;

use App\Models\AssignmentRule;
use App\Models\Lead;
use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;

class LeadAssignmentService
{
    public function claim(Lead $lead, User $user): Lead
    {
        $lead->forceFill([
            'owner_user_id' => $user->id,
            'status' => 'working',
            'pool_entered_at' => null,
        ])->save();

        return $lead->refresh();
    }

    public function release(Lead $lead, ?string $reason = null): Lead
    {
        $lead->forceFill([
            'owner_user_id' => null,
            'status' => 'pooled',
            'pool_entered_at' => now(),
            'lost_reason' => $reason,
        ])->save();

        return $lead->refresh();
    }

    public function assignByRules(Lead $lead): ?Lead
    {
        $rule = AssignmentRule::query()
            ->where('tenant_id', $lead->tenant_id)
            ->where('target_type', 'lead')
            ->where('is_active', true)
            ->orderByDesc('priority')
            ->first();

        if (! $rule) {
            return null;
        }

        $userId = collect(Arr::wrap($rule->user_ids))
            ->filter()
            ->first();

        if (! $userId && $rule->department_id) {
            $userId = User::query()
                ->whereHas('tenants', fn ($query) => $query->where('tenants.id', $lead->tenant_id))
                ->whereHas('roles')
                ->value('id');
        }

        if (! $userId) {
            $userId = Auth::id();
        }

        return $userId ? $this->claim($lead, User::findOrFail($userId)) : null;
    }
}

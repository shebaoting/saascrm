<?php

namespace App\Services\Crm;

use App\Models\Customer;
use App\Models\CustomerPoolHistory;
use App\Models\CustomerPoolRule;
use App\Models\Lead;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class CustomerPoolService
{
    public function claimCustomer(Customer $customer, User $user): Customer
    {
        $this->ensureClaimLimit($customer->tenant_id, 'customer', $user);

        $fromUserId = $customer->owner_user_id;

        $customer->forceFill([
            'owner_user_id' => $user->id,
            'lifecycle_stage' => $customer->lifecycle_stage === 'pooled' ? 'active' : $customer->lifecycle_stage,
            'pool_entered_at' => null,
        ])->save();

        $this->history($customer->tenant_id, Customer::class, $customer->id, 'claim', $fromUserId, $user->id, '手动领取');

        return $customer->refresh();
    }

    public function releaseCustomer(Customer $customer, ?string $reason = null): Customer
    {
        $fromUserId = $customer->owner_user_id;

        $customer->forceFill([
            'owner_user_id' => null,
            'lifecycle_stage' => 'pooled',
            'pool_entered_at' => now(),
        ])->save();

        $this->history($customer->tenant_id, Customer::class, $customer->id, 'release', $fromUserId, null, $reason ?: '手动释放');

        return $customer->refresh();
    }

    public function claimLead(Lead $lead, User $user): void
    {
        $this->ensureClaimLimit($lead->tenant_id, 'lead', $user);

        $this->history($lead->tenant_id, Lead::class, $lead->id, 'claim', $lead->owner_user_id, $user->id, '线索领取');
    }

    public function releaseLead(Lead $lead, ?string $reason = null): void
    {
        $this->history($lead->tenant_id, Lead::class, $lead->id, 'release', $lead->owner_user_id, null, $reason ?: '线索释放');
    }

    public function recycleStaleCustomers(int $tenantId): int
    {
        $count = 0;

        CustomerPoolRule::query()
            ->where('tenant_id', $tenantId)
            ->where('target_type', 'customer')
            ->where('is_active', true)
            ->get()
            ->each(function (CustomerPoolRule $rule) use (&$count): void {
                Customer::query()
                    ->where('tenant_id', $rule->tenant_id)
                    ->whereNotNull('owner_user_id')
                    ->where('lifecycle_stage', '!=', 'pooled')
                    ->where(function ($query) use ($rule): void {
                        $query->whereNull('last_activity_at')
                            ->orWhere('last_activity_at', '<=', now()->subDays((int) $rule->inactive_days));
                    })
                    ->chunkById(100, function ($customers) use (&$count, $rule): void {
                        foreach ($customers as $customer) {
                            $this->releaseCustomer($customer, $rule->name);
                            $count++;
                        }
                    });
            });

        return $count;
    }

    private function ensureClaimLimit(int $tenantId, string $targetType, User $user): void
    {
        $limit = CustomerPoolRule::query()
            ->where('tenant_id', $tenantId)
            ->where('target_type', $targetType)
            ->where('is_active', true)
            ->whereNotNull('max_claim_daily')
            ->min('max_claim_daily');

        if (! $limit) {
            return;
        }

        $claimedToday = CustomerPoolHistory::query()
            ->where('tenant_id', $tenantId)
            ->where('action', 'claim')
            ->where('to_user_id', $user->id)
            ->whereDate('created_at', today())
            ->count();

        if ($claimedToday >= $limit) {
            throw ValidationException::withMessages([
                'claim' => "今天领取数量已达到上限 {$limit} 条。",
            ]);
        }
    }

    private function history(int $tenantId, string $targetType, int $targetId, string $action, ?int $fromUserId, ?int $toUserId, ?string $reason = null): void
    {
        CustomerPoolHistory::create([
            'tenant_id' => $tenantId,
            'target_type' => $targetType,
            'target_id' => $targetId,
            'action' => $action,
            'from_user_id' => $fromUserId,
            'to_user_id' => $toUserId,
            'reason' => $reason,
            'operated_by' => Auth::id(),
            'created_at' => now(),
        ]);
    }
}

<?php

namespace App\Services\Crm;

use App\Models\Tenant;
use App\Models\TenantSubscription;

class SubscriptionLifecycleService
{
    public function run(): array
    {
        $expired = 0;
        $reminded = 0;

        TenantSubscription::query()
            ->with(['tenant.users'])
            ->whereIn('status', ['trialing', 'active'])
            ->whereNotNull('ends_at')
            ->get()
            ->each(function (TenantSubscription $subscription) use (&$expired, &$reminded): void {
                if ($subscription->ends_at->isPast()) {
                    $subscription->forceFill(['status' => 'expired'])->save();
                    $this->notifyTenantAdmins($subscription->tenant, 'subscription_expired', '订阅已到期', '业务功能已受限，请续费后恢复。', $subscription);
                    $expired++;

                    return;
                }

                if ($subscription->ends_at->between(now(), now()->addDays(7))) {
                    $this->notifyTenantAdmins($subscription->tenant, 'subscription_expiring', '订阅即将到期', '订阅将在 '.$subscription->ends_at->format('Y-m-d').' 到期，请及时续费。', $subscription);
                    $reminded++;
                }
            });

        Tenant::query()
            ->whereNotNull('trial_ends_at')
            ->where('trial_ends_at', '<', now())
            ->whereDoesntHave('subscriptions', fn ($query) => $query->whereIn('status', ['trialing', 'active']))
            ->each(function (Tenant $tenant) use (&$expired): void {
                $tenant->forceFill(['status' => 'expired'])->save();
                $this->notifyTenantAdmins($tenant, 'trial_expired', '试用已到期', '业务功能已受限，请开通套餐后恢复。');
                $expired++;
            });

        return ['expired' => $expired, 'reminded' => $reminded];
    }

    private function notifyTenantAdmins(Tenant $tenant, string $type, string $title, string $body, ?TenantSubscription $subscription = null): void
    {
        $tenant->users()
            ->wherePivot('status', 'active')
            ->where(function ($query): void {
                $query->where('tenant_user.is_owner', true)->orWhere('tenant_user.is_admin', true);
            })
            ->get()
            ->each(fn ($user) => app(NotificationService::class)->send(
                $tenant->id,
                $user->id,
                $type,
                $title,
                $body,
                $subscription,
            ));
    }
}

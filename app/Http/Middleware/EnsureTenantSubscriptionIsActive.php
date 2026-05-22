<?php

namespace App\Http\Middleware;

use Closure;
use Filament\Facades\Filament;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTenantSubscriptionIsActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $tenant = Filament::getTenant();
        $subscription = $tenant?->activeSubscription;

        if (! $subscription && $tenant?->trial_ends_at && $tenant->trial_ends_at->isPast()) {
            abort(403, '当前公司试用已到期，请续费或升级套餐后继续使用。');
        }

        if ($subscription && in_array($subscription->status, ['cancelled', 'expired'], true)) {
            abort(403, '当前公司订阅已到期。');
        }

        if ($subscription?->ends_at && $subscription->ends_at->isPast()) {
            abort(403, '当前公司订阅已到期。');
        }

        return $next($request);
    }
}

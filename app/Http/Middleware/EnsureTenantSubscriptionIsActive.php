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

        if ($subscription && in_array($subscription->status, ['cancelled', 'expired'], true)) {
            abort(403, '当前公司订阅已到期。');
        }

        if ($subscription?->ends_at && $subscription->ends_at->isPast()) {
            abort(403, '当前公司订阅已到期。');
        }

        return $next($request);
    }
}

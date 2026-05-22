<?php

namespace App\Http\Middleware;

use Closure;
use Filament\Facades\Filament;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApplyTenantScopes
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($tenant = Filament::getTenant()) {
            app()->instance('currentTenant', $tenant);
        }

        return $next($request);
    }
}

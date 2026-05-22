<?php

namespace App\Http\Middleware;

use Closure;
use Filament\Facades\Filament;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTenantIsActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $tenant = Filament::getTenant();

        abort_if($tenant && in_array($tenant->status, ['suspended', 'cancelled'], true), 403, '当前公司已停用。');

        return $next($request);
    }
}

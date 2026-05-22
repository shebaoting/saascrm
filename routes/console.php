<?php

use App\Models\Tenant;
use App\Services\Crm\CustomerPoolService;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('crm:recycle-stale-customers', function (CustomerPoolService $service): void {
    $total = 0;

    Tenant::query()
        ->whereIn('status', ['trial', 'active'])
        ->each(function (Tenant $tenant) use ($service, &$total): void {
            $total += $service->recycleStaleCustomers($tenant->id);
        });

    $this->info("已自动回收 {$total} 个超期未跟进客户。");
})->purpose('Recycle stale customers into the public pool');

Schedule::command('crm:recycle-stale-customers')->hourly();

<?php

use App\Models\Tenant;
use App\Services\Crm\CustomerPoolService;
use App\Services\Crm\SubscriptionLifecycleService;
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

Artisan::command('crm:check-subscriptions', function (SubscriptionLifecycleService $service): void {
    $result = $service->run();

    $this->info("订阅检查完成：到期 {$result['expired']} 个，提醒 {$result['reminded']} 个。");
})->purpose('Check tenant subscription lifecycle and send reminders');

Schedule::command('crm:check-subscriptions')->dailyAt('09:00');

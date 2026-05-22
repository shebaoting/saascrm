<?php

namespace App\Filament\Platform\Clusters\SubscriptionBilling\Resources\TenantSubscriptions\Pages;

use App\Filament\Platform\Clusters\SubscriptionBilling\Resources\TenantSubscriptions\TenantSubscriptionResource;
use Filament\Resources\Pages\CreateRecord;

class CreateTenantSubscription extends CreateRecord
{
    protected static string $resource = TenantSubscriptionResource::class;
}

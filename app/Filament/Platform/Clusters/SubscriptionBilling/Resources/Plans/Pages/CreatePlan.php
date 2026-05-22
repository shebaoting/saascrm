<?php

namespace App\Filament\Platform\Clusters\SubscriptionBilling\Resources\Plans\Pages;

use App\Filament\Platform\Clusters\SubscriptionBilling\Resources\Plans\PlanResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePlan extends CreateRecord
{
    protected static string $resource = PlanResource::class;
}

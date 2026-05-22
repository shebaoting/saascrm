<?php

namespace App\Filament\Platform\Clusters\SubscriptionBilling\Resources\Plans\Pages;

use App\Filament\Platform\Clusters\SubscriptionBilling\Resources\Plans\PlanResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewPlan extends ViewRecord
{
    protected static string $resource = PlanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}

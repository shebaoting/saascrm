<?php

namespace App\Filament\Platform\Clusters\SubscriptionBilling\Resources\TenantSubscriptions\Pages;

use App\Filament\Platform\Clusters\SubscriptionBilling\Resources\TenantSubscriptions\TenantSubscriptionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageTenantSubscriptions extends ManageRecords
{
    protected static string $resource = TenantSubscriptionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}

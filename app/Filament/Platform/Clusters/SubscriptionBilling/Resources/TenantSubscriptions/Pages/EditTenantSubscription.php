<?php

namespace App\Filament\Platform\Clusters\SubscriptionBilling\Resources\TenantSubscriptions\Pages;

use App\Filament\Platform\Clusters\SubscriptionBilling\Resources\TenantSubscriptions\TenantSubscriptionResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditTenantSubscription extends EditRecord
{
    protected static string $resource = TenantSubscriptionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}

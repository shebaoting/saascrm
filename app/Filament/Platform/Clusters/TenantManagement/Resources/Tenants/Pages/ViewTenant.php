<?php

namespace App\Filament\Platform\Clusters\TenantManagement\Resources\Tenants\Pages;

use App\Filament\Platform\Clusters\TenantManagement\Resources\Tenants\TenantResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewTenant extends ViewRecord
{
    protected static string $resource = TenantResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}

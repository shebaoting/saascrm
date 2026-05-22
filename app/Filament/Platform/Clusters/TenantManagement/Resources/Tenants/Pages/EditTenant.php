<?php

namespace App\Filament\Platform\Clusters\TenantManagement\Resources\Tenants\Pages;

use App\Filament\Platform\Clusters\TenantManagement\Resources\Tenants\TenantResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditTenant extends EditRecord
{
    protected static string $resource = TenantResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}

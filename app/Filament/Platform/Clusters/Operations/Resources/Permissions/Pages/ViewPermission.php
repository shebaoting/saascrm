<?php

namespace App\Filament\Platform\Clusters\Operations\Resources\Permissions\Pages;

use App\Filament\Platform\Clusters\Operations\Resources\Permissions\PermissionResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewPermission extends ViewRecord
{
    protected static string $resource = PermissionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}

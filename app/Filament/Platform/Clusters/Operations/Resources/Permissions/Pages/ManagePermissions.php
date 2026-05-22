<?php

namespace App\Filament\Platform\Clusters\Operations\Resources\Permissions\Pages;

use App\Filament\Platform\Clusters\Operations\Resources\Permissions\PermissionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManagePermissions extends ManageRecords
{
    protected static string $resource = PermissionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}

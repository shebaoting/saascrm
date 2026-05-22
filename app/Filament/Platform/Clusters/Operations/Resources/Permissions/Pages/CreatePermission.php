<?php

namespace App\Filament\Platform\Clusters\Operations\Resources\Permissions\Pages;

use App\Filament\Platform\Clusters\Operations\Resources\Permissions\PermissionResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePermission extends CreateRecord
{
    protected static string $resource = PermissionResource::class;
}

<?php

namespace App\Filament\Clusters\SystemSettings\Resources\Roles\Pages;

use App\Filament\Clusters\SystemSettings\Resources\Roles\RoleResource;
use Filament\Resources\Pages\CreateRecord;

class CreateRole extends CreateRecord
{
    protected static string $resource = RoleResource::class;
}

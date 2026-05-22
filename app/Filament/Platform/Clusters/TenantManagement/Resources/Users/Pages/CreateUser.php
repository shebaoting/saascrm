<?php

namespace App\Filament\Platform\Clusters\TenantManagement\Resources\Users\Pages;

use App\Filament\Platform\Clusters\TenantManagement\Resources\Users\UserResource;
use Filament\Resources\Pages\CreateRecord;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;
}

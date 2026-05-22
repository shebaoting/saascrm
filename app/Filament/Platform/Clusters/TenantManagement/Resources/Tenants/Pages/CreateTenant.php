<?php

namespace App\Filament\Platform\Clusters\TenantManagement\Resources\Tenants\Pages;

use App\Filament\Platform\Clusters\TenantManagement\Resources\Tenants\TenantResource;
use Filament\Resources\Pages\CreateRecord;

class CreateTenant extends CreateRecord
{
    protected static string $resource = TenantResource::class;
}

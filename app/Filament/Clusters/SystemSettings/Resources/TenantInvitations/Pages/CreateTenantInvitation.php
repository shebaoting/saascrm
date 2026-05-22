<?php

namespace App\Filament\Clusters\SystemSettings\Resources\TenantInvitations\Pages;

use App\Filament\Clusters\SystemSettings\Resources\TenantInvitations\TenantInvitationResource;
use Filament\Resources\Pages\CreateRecord;

class CreateTenantInvitation extends CreateRecord
{
    protected static string $resource = TenantInvitationResource::class;
}

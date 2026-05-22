<?php

namespace App\Filament\Clusters\SystemSettings\Resources\TenantInvitations\Pages;

use App\Filament\Clusters\SystemSettings\Resources\TenantInvitations\TenantInvitationResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageTenantInvitations extends ManageRecords
{
    protected static string $resource = TenantInvitationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}

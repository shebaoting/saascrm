<?php

namespace App\Filament\Clusters\SystemSettings\Resources\TenantInvitations\Pages;

use App\Filament\Clusters\SystemSettings\Resources\TenantInvitations\TenantInvitationResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewTenantInvitation extends ViewRecord
{
    protected static string $resource = TenantInvitationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}

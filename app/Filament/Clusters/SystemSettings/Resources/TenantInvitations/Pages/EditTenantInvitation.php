<?php

namespace App\Filament\Clusters\SystemSettings\Resources\TenantInvitations\Pages;

use App\Filament\Clusters\SystemSettings\Resources\TenantInvitations\TenantInvitationResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditTenantInvitation extends EditRecord
{
    protected static string $resource = TenantInvitationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}

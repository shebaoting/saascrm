<?php

namespace App\Filament\Clusters\SystemSettings\Resources\Attachments\Pages;

use App\Filament\Clusters\SystemSettings\Resources\Attachments\AttachmentResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditAttachment extends EditRecord
{
    protected static string $resource = AttachmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}

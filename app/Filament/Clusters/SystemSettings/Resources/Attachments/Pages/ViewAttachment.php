<?php

namespace App\Filament\Clusters\SystemSettings\Resources\Attachments\Pages;

use App\Filament\Clusters\SystemSettings\Resources\Attachments\AttachmentResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewAttachment extends ViewRecord
{
    protected static string $resource = AttachmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}

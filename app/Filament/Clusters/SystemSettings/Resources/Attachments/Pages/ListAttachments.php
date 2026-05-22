<?php

namespace App\Filament\Clusters\SystemSettings\Resources\Attachments\Pages;

use App\Filament\Clusters\SystemSettings\Resources\Attachments\AttachmentResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListAttachments extends ListRecords
{
    protected static string $resource = AttachmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}

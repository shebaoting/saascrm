<?php

namespace App\Filament\Clusters\SystemSettings\Resources\Attachments\Pages;

use App\Filament\Clusters\SystemSettings\Resources\Attachments\AttachmentResource;
use Filament\Resources\Pages\CreateRecord;

class CreateAttachment extends CreateRecord
{
    protected static string $resource = AttachmentResource::class;
}

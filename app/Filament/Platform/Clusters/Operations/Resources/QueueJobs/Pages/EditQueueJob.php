<?php

namespace App\Filament\Platform\Clusters\Operations\Resources\QueueJobs\Pages;

use App\Filament\Platform\Clusters\Operations\Resources\QueueJobs\QueueJobResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditQueueJob extends EditRecord
{
    protected static string $resource = QueueJobResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}

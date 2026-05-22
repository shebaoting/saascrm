<?php

namespace App\Filament\Platform\Clusters\Operations\Resources\QueueJobs\Pages;

use App\Filament\Platform\Clusters\Operations\Resources\QueueJobs\QueueJobResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewQueueJob extends ViewRecord
{
    protected static string $resource = QueueJobResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}

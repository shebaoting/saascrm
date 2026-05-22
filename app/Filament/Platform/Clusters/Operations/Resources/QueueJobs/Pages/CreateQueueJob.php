<?php

namespace App\Filament\Platform\Clusters\Operations\Resources\QueueJobs\Pages;

use App\Filament\Platform\Clusters\Operations\Resources\QueueJobs\QueueJobResource;
use Filament\Resources\Pages\CreateRecord;

class CreateQueueJob extends CreateRecord
{
    protected static string $resource = QueueJobResource::class;
}

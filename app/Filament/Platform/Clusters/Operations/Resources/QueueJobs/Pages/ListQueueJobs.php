<?php

namespace App\Filament\Platform\Clusters\Operations\Resources\QueueJobs\Pages;

use App\Filament\Platform\Clusters\Operations\Resources\QueueJobs\QueueJobResource;
use Filament\Resources\Pages\ListRecords;

class ListQueueJobs extends ListRecords
{
    protected static string $resource = QueueJobResource::class;
}

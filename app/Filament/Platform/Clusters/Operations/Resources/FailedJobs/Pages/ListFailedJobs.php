<?php

namespace App\Filament\Platform\Clusters\Operations\Resources\FailedJobs\Pages;

use App\Filament\Platform\Clusters\Operations\Resources\FailedJobs\FailedJobResource;
use Filament\Resources\Pages\ListRecords;

class ListFailedJobs extends ListRecords
{
    protected static string $resource = FailedJobResource::class;
}

<?php

namespace App\Filament\Platform\Clusters\Operations\Resources\FailedJobs\Pages;

use App\Filament\Platform\Clusters\Operations\Resources\FailedJobs\FailedJobResource;
use Filament\Resources\Pages\ManageRecords;

class ManageFailedJobs extends ManageRecords
{
    protected static string $resource = FailedJobResource::class;
}

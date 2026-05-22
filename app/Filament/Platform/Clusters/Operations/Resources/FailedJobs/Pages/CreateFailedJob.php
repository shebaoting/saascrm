<?php

namespace App\Filament\Platform\Clusters\Operations\Resources\FailedJobs\Pages;

use App\Filament\Platform\Clusters\Operations\Resources\FailedJobs\FailedJobResource;
use Filament\Resources\Pages\CreateRecord;

class CreateFailedJob extends CreateRecord
{
    protected static string $resource = FailedJobResource::class;
}

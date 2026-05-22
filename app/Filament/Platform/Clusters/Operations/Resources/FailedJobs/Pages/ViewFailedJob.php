<?php

namespace App\Filament\Platform\Clusters\Operations\Resources\FailedJobs\Pages;

use App\Filament\Platform\Clusters\Operations\Resources\FailedJobs\FailedJobResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewFailedJob extends ViewRecord
{
    protected static string $resource = FailedJobResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}

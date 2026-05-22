<?php

namespace App\Filament\Clusters\ActivityTasks\Resources\Activities\Pages;

use App\Filament\Clusters\ActivityTasks\Resources\Activities\ActivityResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageActivities extends ManageRecords
{
    protected static string $resource = ActivityResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}

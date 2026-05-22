<?php

namespace App\Filament\Clusters\ActivityTasks\Resources\Tasks\Pages;

use App\Filament\Clusters\ActivityTasks\Resources\Tasks\TaskResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageTasks extends ManageRecords
{
    protected static string $resource = TaskResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}

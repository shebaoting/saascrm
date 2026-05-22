<?php

namespace App\Filament\Clusters\ActivityTasks\Resources\Activities\Pages;

use App\Filament\Clusters\ActivityTasks\Resources\Activities\ActivityResource;
use Filament\Resources\Pages\CreateRecord;

class CreateActivity extends CreateRecord
{
    protected static string $resource = ActivityResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['subject'] = ActivityResource::followUpSubject($data['type'] ?? null);

        return $data;
    }
}

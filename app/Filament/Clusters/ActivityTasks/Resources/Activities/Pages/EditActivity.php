<?php

namespace App\Filament\Clusters\ActivityTasks\Resources\Activities\Pages;

use App\Filament\Clusters\ActivityTasks\Resources\Activities\ActivityResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditActivity extends EditRecord
{
    protected static string $resource = ActivityResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['subject'] = ActivityResource::followUpSubject($data['type'] ?? null);

        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}

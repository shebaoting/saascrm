<?php

namespace App\Filament\Clusters\ActivityTasks\Resources\Notifications\Pages;

use App\Filament\Clusters\ActivityTasks\Resources\Notifications\NotificationResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewNotification extends ViewRecord
{
    protected static string $resource = NotificationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}

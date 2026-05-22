<?php

namespace App\Filament\Clusters\ActivityTasks\Resources\Notifications\Pages;

use App\Filament\Clusters\ActivityTasks\Resources\Notifications\NotificationResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditNotification extends EditRecord
{
    protected static string $resource = NotificationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}

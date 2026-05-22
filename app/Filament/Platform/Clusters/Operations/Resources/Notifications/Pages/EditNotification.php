<?php

namespace App\Filament\Platform\Clusters\Operations\Resources\Notifications\Pages;

use App\Filament\Platform\Clusters\Operations\Resources\Notifications\NotificationResource;
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

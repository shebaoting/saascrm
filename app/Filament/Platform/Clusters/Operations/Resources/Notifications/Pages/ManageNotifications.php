<?php

namespace App\Filament\Platform\Clusters\Operations\Resources\Notifications\Pages;

use App\Filament\Platform\Clusters\Operations\Resources\Notifications\NotificationResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageNotifications extends ManageRecords
{
    protected static string $resource = NotificationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}

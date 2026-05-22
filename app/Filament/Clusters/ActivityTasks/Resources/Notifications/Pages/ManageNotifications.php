<?php

namespace App\Filament\Clusters\ActivityTasks\Resources\Notifications\Pages;

use App\Filament\Clusters\ActivityTasks\Resources\Notifications\NotificationResource;
use App\Services\Crm\NotificationService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ManageRecords;

class ManageNotifications extends ManageRecords
{
    protected static string $resource = NotificationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('mark_all_read')
                ->label('全部标为已读')
                ->icon('heroicon-o-check-circle')
                ->action(function (): void {
                    $count = app(NotificationService::class)->markAllRead(filament()->getTenant()->getKey(), auth()->id());

                    Notification::make()
                        ->success()
                        ->title('通知已更新')
                        ->body("已标记 {$count} 条通知。")
                        ->send();
                }),
        ];
    }
}

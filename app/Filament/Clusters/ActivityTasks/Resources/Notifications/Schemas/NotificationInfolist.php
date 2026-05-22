<?php

namespace App\Filament\Clusters\ActivityTasks\Resources\Notifications\Schemas;

use App\Models\Notification as CrmNotification;
use App\Services\Crm\NotificationService;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class NotificationInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextEntry::make('payload_title')
                ->label('标题')
                ->state(fn (CrmNotification $record): string => app(NotificationService::class)->payload($record)['title']),
            TextEntry::make('payload_body')
                ->label('内容')
                ->state(fn (CrmNotification $record): ?string => app(NotificationService::class)->payload($record)['body'])
                ->columnSpanFull(),
            TextEntry::make('type'),
            TextEntry::make('read_at')
                ->dateTime()
                ->placeholder('未读'),
            TextEntry::make('created_at')
                ->dateTime(),
        ]);
    }
}

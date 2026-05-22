<?php

namespace App\Filament\Clusters\ActivityTasks\Resources\Notifications;

use App\Filament\Clusters\ActivityTasks\ActivityTasksCluster;
use App\Filament\Clusters\ActivityTasks\Resources\Notifications\Pages\ListNotifications;
use App\Filament\Clusters\ActivityTasks\Resources\Notifications\Pages\ViewNotification;
use App\Filament\Clusters\ActivityTasks\Resources\Notifications\Schemas\NotificationInfolist;
use App\Filament\Clusters\ActivityTasks\Resources\Notifications\Tables\NotificationTable;
use App\Models\Notification as CrmNotification;
use App\Models\User;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class NotificationResource extends Resource
{
    protected static ?string $model = CrmNotification::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBell;

    protected static ?string $navigationLabel = '通知中心';

    protected static ?string $modelLabel = '通知';

    protected static ?string $pluralModelLabel = '通知';

    protected static ?string $title = '通知中心';

    protected static bool $hasTitleCaseModelLabel = false;

    protected static ?string $cluster = ActivityTasksCluster::class;

    public static function infolist(Schema $schema): Schema
    {
        return NotificationInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return NotificationTable::configure($table);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('tenant_id', filament()->getTenant()?->getKey())
            ->where('notifiable_type', User::class)
            ->where('notifiable_id', auth()->id());
    }

    public static function getNavigationBadge(): ?string
    {
        $count = static::getEloquentQuery()->whereNull('read_at')->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getPages(): array
    {
        return [
            'index' => ListNotifications::route('/'),
            'view' => ViewNotification::route('/{record}'),
        ];
    }
}

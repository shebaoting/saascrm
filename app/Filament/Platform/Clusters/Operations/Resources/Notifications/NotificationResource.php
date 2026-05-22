<?php

namespace App\Filament\Platform\Clusters\Operations\Resources\Notifications;

use App\Filament\Platform\Clusters\Operations\OperationsCluster;
use App\Filament\Platform\Clusters\Operations\Resources\Notifications\Pages\CreateNotification;
use App\Filament\Platform\Clusters\Operations\Resources\Notifications\Pages\EditNotification;
use App\Filament\Platform\Clusters\Operations\Resources\Notifications\Pages\ListNotifications;
use App\Filament\Platform\Clusters\Operations\Resources\Notifications\Pages\ViewNotification;
use App\Filament\Platform\Clusters\Operations\Resources\Notifications\Schemas\NotificationForm;
use App\Filament\Platform\Clusters\Operations\Resources\Notifications\Schemas\NotificationInfolist;
use App\Filament\Platform\Clusters\Operations\Resources\Notifications\Tables\NotificationTable;
use App\Models\Notification;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class NotificationResource extends Resource
{
    protected static ?string $model = Notification::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $navigationLabel = '通知';

    protected static ?string $modelLabel = '通知';

    protected static ?string $pluralModelLabel = '通知';

    protected static ?string $title = '通知';

    protected static bool $hasTitleCaseModelLabel = false;

    protected static ?string $cluster = OperationsCluster::class;

    protected static ?string $recordTitleAttribute = 'type';

    public static function form(Schema $schema): Schema
    {
        return NotificationForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return NotificationInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return NotificationTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListNotifications::route('/'),
            'create' => CreateNotification::route('/create'),
            'view' => ViewNotification::route('/{record}'),
            'edit' => EditNotification::route('/{record}/edit'),
        ];
    }
}

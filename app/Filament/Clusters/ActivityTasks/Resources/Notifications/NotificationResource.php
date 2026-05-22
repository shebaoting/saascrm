<?php

namespace App\Filament\Clusters\ActivityTasks\Resources\Notifications;

use App\Filament\Clusters\ActivityTasks\ActivityTasksCluster;
use App\Filament\Clusters\ActivityTasks\Resources\Notifications\Pages\ManageNotifications;
use App\Models\Notification as CrmNotification;
use App\Models\User;
use App\Services\Crm\NotificationService;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
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

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                IconColumn::make('read_at')
                    ->label('已读')
                    ->boolean()
                    ->state(fn (CrmNotification $record): bool => filled($record->read_at)),
                TextColumn::make('payload_title')
                    ->label('标题')
                    ->state(fn (CrmNotification $record): string => app(NotificationService::class)->payload($record)['title'])
                    ->searchable(query: fn (Builder $query, string $search): Builder => $query->where('data', 'like', "%{$search}%")),
                TextColumn::make('payload_body')
                    ->label('内容')
                    ->state(fn (CrmNotification $record): ?string => app(NotificationService::class)->payload($record)['body'])
                    ->limit(60),
                TextColumn::make('type')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('read_at')
                    ->dateTime()
                    ->sortable()
                    ->placeholder('未读'),
            ])
            ->filters([
                TernaryFilter::make('read_at')
                    ->label('阅读状态')
                    ->nullable()
                    ->placeholder('全部')
                    ->trueLabel('已读')
                    ->falseLabel('未读'),
            ])
            ->recordActions([
                Action::make('mark_read')
                    ->label('标为已读')
                    ->icon('heroicon-o-check')
                    ->visible(fn (CrmNotification $record): bool => blank($record->read_at))
                    ->action(fn (CrmNotification $record): CrmNotification => app(NotificationService::class)->markRead($record)),
                ViewAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
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
            'index' => ManageNotifications::route('/'),
        ];
    }
}

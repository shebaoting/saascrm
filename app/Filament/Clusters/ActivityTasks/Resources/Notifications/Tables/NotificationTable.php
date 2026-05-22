<?php

namespace App\Filament\Clusters\ActivityTasks\Resources\Notifications\Tables;

use App\Models\Notification as CrmNotification;
use App\Services\Crm\NotificationService;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class NotificationTable
{
    public static function configure(Table $table): Table
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
}

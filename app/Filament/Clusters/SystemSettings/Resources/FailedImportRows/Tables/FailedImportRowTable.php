<?php

namespace App\Filament\Clusters\SystemSettings\Resources\FailedImportRows\Tables;

use App\Models\FailedImportRow;
use App\Services\Crm\DataPortService;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class FailedImportRowTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('validation_error')
            ->columns([
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('import.file_name')
                    ->searchable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                Action::make('retry')
                    ->label('重试')
                    ->icon('heroicon-o-arrow-path')
                    ->requiresConfirmation()
                    ->action(function (FailedImportRow $record): void {
                        $ok = app(DataPortService::class)->retryFailedRow($record, auth()->user());
                        $notification = Notification::make()
                            ->title($ok ? '重试成功' : '重试失败');

                        ($ok ? $notification->success() : $notification->danger())->send();
                    }),
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}

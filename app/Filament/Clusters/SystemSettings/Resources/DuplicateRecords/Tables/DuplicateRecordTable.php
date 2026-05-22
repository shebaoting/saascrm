<?php

namespace App\Filament\Clusters\SystemSettings\Resources\DuplicateRecords\Tables;

use App\Models\DuplicateRecord;
use App\Services\Crm\DuplicateDetectionService;
use App\Support\Filament\CrmUi;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class DuplicateRecordTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('field_value')
            ->columns([
                TextColumn::make('target_type')
                    ->searchable(),
                TextColumn::make('field_name')
                    ->searchable(),
                TextColumn::make('field_value')
                    ->searchable(),
                TextColumn::make('matched_type')
                    ->searchable(),
                TextColumn::make('matched_id')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('status')
                    ->searchable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options(CrmUi::options('duplicate.status')),
                SelectFilter::make('target_type')
                    ->options([
                        'lead' => '线索',
                        'customer' => '客户',
                    ]),
            ])
            ->recordActions([
                Action::make('ignore')
                    ->label('忽略')
                    ->icon('heroicon-o-eye-slash')
                    ->visible(fn (DuplicateRecord $record): bool => $record->status === 'pending')
                    ->requiresConfirmation()
                    ->action(function (DuplicateRecord $record): void {
                        app(DuplicateDetectionService::class)->ignore($record);

                        Notification::make()->success()->title('已忽略该疑似重复')->send();
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

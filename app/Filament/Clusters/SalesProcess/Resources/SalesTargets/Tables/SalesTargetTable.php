<?php

namespace App\Filament\Clusters\SalesProcess\Resources\SalesTargets\Tables;

use App\Filament\Clusters\SalesProcess\Resources\SalesTargets\SalesTargetResource;
use App\Models\SalesTarget;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SalesTargetTable
{
    public static function configure(Table $table, string $resourceClass = SalesTargetResource::class): Table
    {
        return $table
            ->recordTitleAttribute('target_type')
            ->columns([
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('target_type')
                    ->searchable(),
                TextColumn::make('target_id')
                    ->label('目标对象')
                    ->formatStateUsing(fn ($state, SalesTarget $record): string => $resourceClass::targetDisplay($record))
                    ->sortable(),
                TextColumn::make('period_type')
                    ->searchable(),
                TextColumn::make('period_start')
                    ->date()
                    ->sortable(),
                TextColumn::make('period_end')
                    ->date()
                    ->sortable(),
                TextColumn::make('target_amount')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('target_payment_amount')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('target_customer_count')
                    ->numeric()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
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

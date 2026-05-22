<?php

namespace App\Filament\Platform\Clusters\SubscriptionBilling\Resources\Plans\Tables;

use App\Services\Crm\PlanLimitService;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PlanTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('code')
                    ->searchable(),
                TextColumn::make('price_monthly')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('price_yearly')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('max_users')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('max_leads')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('max_customers')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('max_storage_mb')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('max_custom_fields')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('max_automation_rules')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('max_imports_daily')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('max_exports_daily')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('features')
                    ->formatStateUsing(fn (?array $state): string => collect($state ?: [])
                        ->filter()
                        ->keys()
                        ->map(fn (string $key): string => PlanLimitService::featureLabels()[$key] ?? $key)
                        ->join('、'))
                    ->wrap()
                    ->toggleable(isToggledHiddenByDefault: true),
                IconColumn::make('is_active')
                    ->boolean(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
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

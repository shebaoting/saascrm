<?php

namespace App\Filament\Clusters\SystemSettings\Resources\BusinessNumberRules\Tables;

use App\Services\Crm\BusinessNumberService;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class BusinessNumberRuleTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('module')
                    ->label('业务对象')
                    ->formatStateUsing(fn (string $state): string => BusinessNumberService::moduleLabels()[$state] ?? $state)
                    ->searchable(),
                TextColumn::make('name')
                    ->label('规则名称')
                    ->searchable(),
                TextColumn::make('pattern')
                    ->label('模板')
                    ->searchable(),
                TextColumn::make('current_sequence')
                    ->label('当前流水')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('reset_period')
                    ->label('重置周期')
                    ->sortable(),
                IconColumn::make('is_active')
                    ->label('启用')
                    ->boolean(),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}

<?php

namespace App\Filament\Clusters\SystemSettings\Resources\FieldHistories\Tables;

use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class FieldHistoryTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')
                    ->label('时间')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('model_type')
                    ->label('对象')
                    ->formatStateUsing(fn (?string $state): string => class_basename((string) $state))
                    ->searchable(),
                TextColumn::make('model_id')
                    ->label('记录ID')
                    ->sortable(),
                TextColumn::make('field')
                    ->label('字段')
                    ->searchable(),
                TextColumn::make('old_value.value')
                    ->label('修改前')
                    ->limit(30),
                TextColumn::make('new_value.value')
                    ->label('修改后')
                    ->limit(30),
            ])
            ->filters([
                SelectFilter::make('model_type')
                    ->label('对象')
                    ->options([
                        \App\Models\Lead::class => '线索',
                        \App\Models\Customer::class => '客户',
                        \App\Models\Contact::class => '联系人',
                        \App\Models\Opportunity::class => '商机',
                        \App\Models\Quote::class => '报价',
                        \App\Models\Order::class => '订单',
                    ]),
            ])
            ->recordActions([
                ViewAction::make(),
            ])
            ->defaultSort('created_at', 'desc');
    }
}

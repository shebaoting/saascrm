<?php

namespace App\Filament\Clusters\ActivityTasks\Resources\Activities\Tables;

use App\Models\Activity;
use App\Support\Filament\CrmUi;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class ActivityTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('subject')
            ->columns([
                TextColumn::make('content')
                    ->label('跟进内容')
                    ->state(fn (Activity $record): string => CrmUi::followUpContent($record))
                    ->searchable(['subject', 'content'])
                    ->limit(80)
                    ->wrap(),
                TextColumn::make('type')
                    ->label('类型')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('customer.name')
                    ->label('客户')
                    ->searchable(),
                TextColumn::make('contact.name')
                    ->label('联系人')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('occurred_at')
                    ->label('跟进时间')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('next_follow_at')
                    ->label('下次跟进')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('owner.name')
                    ->label('记录人')
                    ->searchable(),
                TextColumn::make('created_at')
                    ->label('创建时间')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label('更新时间')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('deleted_at')
                    ->label('删除时间')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('lead.company_name')
                    ->label('线索')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('opportunity.name')
                    ->label('商机')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('direction')
                    ->label('方向')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
                RestoreAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}

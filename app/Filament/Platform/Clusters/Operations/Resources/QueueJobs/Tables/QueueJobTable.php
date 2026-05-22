<?php

namespace App\Filament\Platform\Clusters\Operations\Resources\QueueJobs\Tables;

use Carbon\Carbon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class QueueJobTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),
                TextColumn::make('queue')
                    ->label('队列')
                    ->searchable(),
                TextColumn::make('attempts')
                    ->label('尝试次数')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('available_at')
                    ->label('可执行时间')
                    ->formatStateUsing(fn (int $state): string => Carbon::createFromTimestamp($state)->format('Y-m-d H:i:s'))
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('创建时间')
                    ->formatStateUsing(fn (int $state): string => Carbon::createFromTimestamp($state)->format('Y-m-d H:i:s'))
                    ->sortable(),
            ])
            ->defaultSort('id', 'desc');
    }
}

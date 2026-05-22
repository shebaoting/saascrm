<?php

namespace App\Filament\Platform\Clusters\Operations\Resources\QueueJobs;

use App\Filament\Platform\Clusters\Operations\OperationsCluster;
use App\Filament\Platform\Clusters\Operations\Resources\QueueJobs\Pages\ManageQueueJobs;
use App\Models\QueueJob;
use BackedEnum;
use Carbon\Carbon;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class QueueJobResource extends Resource
{
    protected static ?string $model = QueueJob::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCircleStack;

    protected static ?string $navigationLabel = '队列状态';

    protected static ?string $modelLabel = '队列任务';

    protected static ?string $pluralModelLabel = '队列任务';

    protected static ?string $title = '队列状态';

    protected static bool $hasTitleCaseModelLabel = false;

    protected static ?string $cluster = OperationsCluster::class;

    public static function canCreate(): bool
    {
        return false;
    }

    public static function table(Table $table): Table
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

    public static function getPages(): array
    {
        return [
            'index' => ManageQueueJobs::route('/'),
        ];
    }
}

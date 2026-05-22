<?php

namespace App\Filament\Platform\Clusters\Operations\Resources\QueueJobs;

use App\Filament\Platform\Clusters\Operations\OperationsCluster;
use App\Filament\Platform\Clusters\Operations\Resources\QueueJobs\Pages\ListQueueJobs;
use App\Filament\Platform\Clusters\Operations\Resources\QueueJobs\Tables\QueueJobTable;
use App\Models\QueueJob;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
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
        return QueueJobTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListQueueJobs::route('/'),
        ];
    }
}

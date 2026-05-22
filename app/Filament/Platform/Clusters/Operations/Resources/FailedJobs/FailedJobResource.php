<?php

namespace App\Filament\Platform\Clusters\Operations\Resources\FailedJobs;

use App\Filament\Platform\Clusters\Operations\OperationsCluster;
use App\Filament\Platform\Clusters\Operations\Resources\FailedJobs\Pages\ListFailedJobs;
use App\Filament\Platform\Clusters\Operations\Resources\FailedJobs\Tables\FailedJobTable;
use App\Models\FailedJob;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class FailedJobResource extends Resource
{
    protected static ?string $model = FailedJob::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedExclamationTriangle;

    protected static ?string $navigationLabel = '失败任务';

    protected static ?string $modelLabel = '失败任务';

    protected static ?string $pluralModelLabel = '失败任务';

    protected static ?string $title = '失败任务';

    protected static bool $hasTitleCaseModelLabel = false;

    protected static ?string $cluster = OperationsCluster::class;

    public static function canCreate(): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        return FailedJobTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListFailedJobs::route('/'),
        ];
    }
}

<?php

namespace App\Filament\Clusters\ActivityTasks;

use BackedEnum;
use Filament\Clusters\Cluster;
use Filament\Support\Icons\Heroicon;

class ActivityTasksCluster extends Cluster
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSquares2x2;

    protected static ?string $navigationLabel = '活动任务';

    protected static ?string $title = '活动任务';

    protected static ?string $clusterBreadcrumb = '活动任务';

    protected static ?string $slug = 'activity-tasks';

    protected static ?int $navigationSort = 7;
}

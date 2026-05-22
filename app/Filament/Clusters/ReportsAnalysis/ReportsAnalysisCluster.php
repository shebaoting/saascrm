<?php

namespace App\Filament\Clusters\ReportsAnalysis;

use BackedEnum;
use Filament\Clusters\Cluster;
use Filament\Support\Icons\Heroicon;

class ReportsAnalysisCluster extends Cluster
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSquares2x2;

    protected static ?string $navigationLabel = '报表分析';

    protected static ?string $title = '报表分析';

    protected static ?string $clusterBreadcrumb = '报表分析';

    protected static ?string $slug = 'reports-analysis';

    protected static ?int $navigationSort = 9;
}

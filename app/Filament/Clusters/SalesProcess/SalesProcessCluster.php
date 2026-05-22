<?php

namespace App\Filament\Clusters\SalesProcess;

use BackedEnum;
use Filament\Clusters\Cluster;
use Filament\Support\Icons\Heroicon;

class SalesProcessCluster extends Cluster
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSquares2x2;

    protected static ?string $navigationLabel = '销售过程';

    protected static ?string $title = '销售过程';

    protected static ?string $clusterBreadcrumb = '销售过程';

    protected static ?string $slug = 'sales-process';

    protected static ?int $navigationSort = 4;
}

<?php

namespace App\Filament\Platform\Clusters\Operations;

use BackedEnum;
use Filament\Clusters\Cluster;
use Filament\Support\Icons\Heroicon;

class OperationsCluster extends Cluster
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSquares2x2;

    protected static ?string $navigationLabel = '运维';

    protected static ?string $title = '运维';

    protected static ?string $clusterBreadcrumb = '运维';

    protected static ?string $slug = 'operations';

    protected static ?int $navigationSort = 4;
}

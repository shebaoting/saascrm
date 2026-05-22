<?php

namespace App\Filament\Clusters\LeadCenter;

use BackedEnum;
use Filament\Clusters\Cluster;
use Filament\Support\Icons\Heroicon;

class LeadCenterCluster extends Cluster
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSquares2x2;

    protected static ?string $navigationLabel = '线索中心';

    protected static ?string $title = '线索中心';

    protected static ?string $clusterBreadcrumb = '线索中心';

    protected static ?string $slug = 'lead-center';

    protected static ?int $navigationSort = 2;
}

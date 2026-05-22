<?php

namespace App\Filament\Clusters\CustomerCenter;

use BackedEnum;
use Filament\Clusters\Cluster;
use Filament\Support\Icons\Heroicon;

class CustomerCenterCluster extends Cluster
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSquares2x2;

    protected static ?string $navigationLabel = '客户中心';

    protected static ?string $title = '客户中心';

    protected static ?string $clusterBreadcrumb = '客户中心';

    protected static ?string $slug = 'customer-center';

    protected static ?int $navigationSort = 3;
}

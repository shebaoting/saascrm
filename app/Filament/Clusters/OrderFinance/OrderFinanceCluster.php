<?php

namespace App\Filament\Clusters\OrderFinance;

use BackedEnum;
use Filament\Clusters\Cluster;
use Filament\Support\Icons\Heroicon;

class OrderFinanceCluster extends Cluster
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSquares2x2;

    protected static ?string $navigationLabel = '订单财务';

    protected static ?string $title = '订单财务';

    protected static ?string $clusterBreadcrumb = '订单财务';

    protected static ?string $slug = 'order-finance';

    protected static ?int $navigationSort = 6;
}

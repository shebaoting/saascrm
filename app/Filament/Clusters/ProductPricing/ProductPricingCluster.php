<?php

namespace App\Filament\Clusters\ProductPricing;

use BackedEnum;
use Filament\Clusters\Cluster;
use Filament\Support\Icons\Heroicon;

class ProductPricingCluster extends Cluster
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSquares2x2;

    protected static ?string $navigationLabel = '商品价格';

    protected static ?string $title = '商品价格';

    protected static ?string $clusterBreadcrumb = '商品价格';

    protected static ?string $slug = 'product-pricing';

    protected static ?int $navigationSort = 5;
}

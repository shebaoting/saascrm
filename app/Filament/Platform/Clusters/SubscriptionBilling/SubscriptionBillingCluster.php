<?php

namespace App\Filament\Platform\Clusters\SubscriptionBilling;

use BackedEnum;
use Filament\Clusters\Cluster;
use Filament\Support\Icons\Heroicon;

class SubscriptionBillingCluster extends Cluster
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSquares2x2;

    protected static ?string $navigationLabel = '订阅计费';

    protected static ?string $title = '订阅计费';

    protected static ?string $clusterBreadcrumb = '订阅计费';

    protected static ?string $slug = 'subscription-billing';

    protected static ?int $navigationSort = 3;
}

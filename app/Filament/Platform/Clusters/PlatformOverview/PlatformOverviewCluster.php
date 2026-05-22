<?php

namespace App\Filament\Platform\Clusters\PlatformOverview;

use BackedEnum;
use Filament\Clusters\Cluster;
use Filament\Support\Icons\Heroicon;

class PlatformOverviewCluster extends Cluster
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSquares2x2;

    protected static ?string $navigationLabel = '平台概览';

    protected static ?string $title = '平台概览';

    protected static ?string $clusterBreadcrumb = '平台概览';

    protected static ?string $slug = 'platform-overview';

    protected static ?int $navigationSort = 1;
}

<?php

namespace App\Filament\Platform\Clusters\TenantManagement;

use BackedEnum;
use Filament\Clusters\Cluster;
use Filament\Support\Icons\Heroicon;

class TenantManagementCluster extends Cluster
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSquares2x2;

    protected static ?string $navigationLabel = '租户管理';

    protected static ?string $title = '租户管理';

    protected static ?string $clusterBreadcrumb = '租户管理';

    protected static ?string $slug = 'tenant-management';

    protected static ?int $navigationSort = 2;
}

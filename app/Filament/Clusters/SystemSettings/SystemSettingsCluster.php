<?php

namespace App\Filament\Clusters\SystemSettings;

use BackedEnum;
use Filament\Clusters\Cluster;
use Filament\Support\Icons\Heroicon;

class SystemSettingsCluster extends Cluster
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSquares2x2;

    protected static ?string $navigationLabel = '系统设置';

    protected static ?string $title = '系统设置';

    protected static ?string $clusterBreadcrumb = '系统设置';

    protected static ?string $slug = 'system-settings';

    protected static ?int $navigationSort = 10;
}

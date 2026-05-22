<?php

namespace App\Filament\Clusters\Workspace;

use BackedEnum;
use Filament\Clusters\Cluster;
use Filament\Support\Icons\Heroicon;

class WorkspaceCluster extends Cluster
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSquares2x2;

    protected static ?string $navigationLabel = '工作台';

    protected static ?string $title = '工作台';

    protected static ?string $clusterBreadcrumb = '工作台';

    protected static ?string $slug = 'workspace';

    protected static ?int $navigationSort = 1;
}

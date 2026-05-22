<?php

namespace App\Filament\Clusters\KnowledgeBase;

use BackedEnum;
use Filament\Clusters\Cluster;
use Filament\Support\Icons\Heroicon;

class KnowledgeBaseCluster extends Cluster
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSquares2x2;

    protected static ?string $navigationLabel = '知识库';

    protected static ?string $title = '知识库';

    protected static ?string $clusterBreadcrumb = '知识库';

    protected static ?string $slug = 'knowledge-base';

    protected static ?int $navigationSort = 8;
}

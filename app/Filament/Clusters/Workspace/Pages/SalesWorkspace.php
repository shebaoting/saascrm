<?php

namespace App\Filament\Clusters\Workspace\Pages;

use App\Filament\Clusters\Workspace\WorkspaceCluster;
use App\Support\CrmMetrics;
use Filament\Pages\Page;

class SalesWorkspace extends Page
{
    protected string $view = 'filament.pages.crm-metric-page';

    protected static ?string $cluster = WorkspaceCluster::class;

    protected static ?string $navigationLabel = '销售工作台';

    protected static ?string $title = '销售工作台';

    protected static ?int $navigationSort = 1;

    protected function getViewData(): array
    {
        return CrmMetrics::salesWorkspace();
    }
}

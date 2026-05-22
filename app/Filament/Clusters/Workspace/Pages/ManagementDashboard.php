<?php

namespace App\Filament\Clusters\Workspace\Pages;

use App\Filament\Clusters\Workspace\WorkspaceCluster;
use App\Support\CrmMetrics;
use Filament\Pages\Page;

class ManagementDashboard extends Page
{
    protected string $view = 'filament.pages.crm-metric-page';

    protected static ?string $cluster = WorkspaceCluster::class;

    protected static ?string $navigationLabel = '管理驾驶舱';

    protected static ?string $title = '管理驾驶舱';

    protected static ?int $navigationSort = 2;

    protected function getViewData(): array
    {
        return CrmMetrics::managementDashboard();
    }
}

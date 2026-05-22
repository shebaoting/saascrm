<?php

namespace App\Filament\Clusters\ReportsAnalysis\Pages;

use App\Filament\Clusters\ReportsAnalysis\ReportsAnalysisCluster;
use App\Support\CrmMetrics;
use Filament\Pages\Page;

class SalesTargetReport extends Page
{
    protected string $view = 'filament.pages.crm-metric-page';

    protected static ?string $cluster = ReportsAnalysisCluster::class;

    protected static ?string $navigationLabel = '目标完成';

    protected static ?string $title = '目标完成';

    protected static ?int $navigationSort = 5;

    protected function getViewData(): array
    {
        return CrmMetrics::salesTargetReport();
    }
}

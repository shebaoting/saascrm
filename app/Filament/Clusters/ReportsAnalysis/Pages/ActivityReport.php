<?php

namespace App\Filament\Clusters\ReportsAnalysis\Pages;

use App\Filament\Clusters\ReportsAnalysis\ReportsAnalysisCluster;
use App\Support\CrmMetrics;
use Filament\Pages\Page;

class ActivityReport extends Page
{
    protected string $view = 'filament.pages.crm-metric-page';

    protected static ?string $cluster = ReportsAnalysisCluster::class;

    protected static ?string $navigationLabel = '跟进效率';

    protected static ?string $title = '跟进效率';

    protected static ?int $navigationSort = 3;

    protected function getViewData(): array
    {
        return CrmMetrics::activityReport();
    }
}

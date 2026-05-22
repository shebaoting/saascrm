<?php

namespace App\Filament\Clusters\ReportsAnalysis\Pages;

use App\Filament\Clusters\ReportsAnalysis\ReportsAnalysisCluster;
use App\Support\CrmMetrics;
use Filament\Pages\Page;

class PoolReport extends Page
{
    protected string $view = 'filament.pages.crm-metric-page';

    protected static ?string $cluster = ReportsAnalysisCluster::class;

    protected static ?string $navigationLabel = '公海效率';

    protected static ?string $title = '公海效率';

    protected static ?int $navigationSort = 7;

    protected function getViewData(): array
    {
        return CrmMetrics::poolReport();
    }
}

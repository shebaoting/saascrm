<?php

namespace App\Filament\Clusters\ReportsAnalysis\Pages;

use App\Filament\Clusters\ReportsAnalysis\ReportsAnalysisCluster;
use App\Support\CrmMetrics;
use Filament\Pages\Page;

class PipelineReport extends Page
{
    protected string $view = 'filament.pages.crm-metric-page';

    protected static ?string $cluster = ReportsAnalysisCluster::class;

    protected static ?string $navigationLabel = '销售漏斗';

    protected static ?string $title = '销售漏斗';

    protected static ?int $navigationSort = 1;

    protected function getViewData(): array
    {
        return CrmMetrics::pipelineReport();
    }
}

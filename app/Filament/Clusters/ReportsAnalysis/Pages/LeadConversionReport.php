<?php

namespace App\Filament\Clusters\ReportsAnalysis\Pages;

use App\Filament\Clusters\ReportsAnalysis\ReportsAnalysisCluster;
use App\Support\CrmMetrics;
use Filament\Pages\Page;

class LeadConversionReport extends Page
{
    protected string $view = 'filament.pages.crm-metric-page';

    protected static ?string $cluster = ReportsAnalysisCluster::class;

    protected static ?string $navigationLabel = '线索转化';

    protected static ?string $title = '线索转化';

    protected static ?int $navigationSort = 6;

    protected function getViewData(): array
    {
        return CrmMetrics::leadConversionReport();
    }
}

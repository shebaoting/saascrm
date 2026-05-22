<?php

namespace App\Filament\Clusters\ReportsAnalysis\Pages;

use App\Filament\Clusters\ReportsAnalysis\ReportsAnalysisCluster;
use App\Support\CrmMetrics;
use Filament\Pages\Page;

class ProductSalesReport extends Page
{
    protected string $view = 'filament.pages.crm-metric-page';

    protected static ?string $cluster = ReportsAnalysisCluster::class;

    protected static ?string $navigationLabel = '商品销售';

    protected static ?string $title = '商品销售';

    protected static ?int $navigationSort = 8;

    protected function getViewData(): array
    {
        return CrmMetrics::productSalesReport();
    }
}

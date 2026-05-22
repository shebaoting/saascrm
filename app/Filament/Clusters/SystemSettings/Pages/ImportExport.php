<?php

namespace App\Filament\Clusters\SystemSettings\Pages;

use App\Filament\Clusters\SystemSettings\SystemSettingsCluster;
use App\Support\CrmMetrics;
use Filament\Pages\Page;

class ImportExport extends Page
{
    protected string $view = 'filament.pages.crm-metric-page';

    protected static ?string $cluster = SystemSettingsCluster::class;

    protected static ?string $navigationLabel = '导入导出';

    protected static ?string $title = '导入导出';

    protected static ?int $navigationSort = 91;

    protected function getViewData(): array
    {
        return CrmMetrics::importExport();
    }
}

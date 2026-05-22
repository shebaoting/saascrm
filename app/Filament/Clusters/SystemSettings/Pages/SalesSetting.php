<?php

namespace App\Filament\Clusters\SystemSettings\Pages;

use App\Filament\Clusters\SystemSettings\SystemSettingsCluster;
use App\Support\CrmMetrics;
use Filament\Pages\Page;

class SalesSetting extends Page
{
    protected string $view = 'filament.pages.crm-metric-page';

    protected static ?string $cluster = SystemSettingsCluster::class;

    protected static ?string $navigationLabel = '销售设置';

    protected static ?string $title = '销售设置';

    protected static ?int $navigationSort = 90;

    protected function getViewData(): array
    {
        return CrmMetrics::salesSetting();
    }
}

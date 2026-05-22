<?php

namespace App\Filament\Platform\Clusters\PlatformOverview\Pages;

use App\Filament\Platform\Clusters\PlatformOverview\PlatformOverviewCluster;
use App\Support\CrmMetrics;
use Filament\Pages\Page;

class PlatformOverview extends Page
{
    protected string $view = 'filament.pages.crm-metric-page';

    protected static ?string $cluster = PlatformOverviewCluster::class;

    protected static ?string $navigationLabel = '总览';

    protected static ?string $title = '总览';

    protected static ?int $navigationSort = 1;

    protected function getViewData(): array
    {
        return CrmMetrics::platformOverview();
    }
}

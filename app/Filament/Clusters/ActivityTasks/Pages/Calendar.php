<?php

namespace App\Filament\Clusters\ActivityTasks\Pages;

use App\Filament\Clusters\ActivityTasks\ActivityTasksCluster;
use App\Support\CrmMetrics;
use Filament\Pages\Page;

class Calendar extends Page
{
    protected string $view = 'filament.pages.crm-metric-page';

    protected static ?string $cluster = ActivityTasksCluster::class;

    protected static ?string $navigationLabel = '日历';

    protected static ?string $title = '日历';

    protected static ?int $navigationSort = 30;

    protected function getViewData(): array
    {
        return CrmMetrics::calendar();
    }
}

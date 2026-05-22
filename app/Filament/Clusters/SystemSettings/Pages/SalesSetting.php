<?php

namespace App\Filament\Clusters\SystemSettings\Pages;

use App\Filament\Clusters\CustomerCenter\Resources\CustomerPoolRules\CustomerPoolRuleResource;
use App\Filament\Clusters\LeadCenter\Resources\AssignmentRules\AssignmentRuleResource;
use App\Filament\Clusters\LeadCenter\Resources\LeadScoreRules\LeadScoreRuleResource;
use App\Filament\Clusters\SalesProcess\Resources\Pipelines\PipelineResource;
use App\Filament\Clusters\SalesProcess\Resources\PipelineStages\PipelineStageResource;
use App\Filament\Clusters\SystemSettings\Resources\AutomationRules\AutomationRuleResource;
use App\Filament\Clusters\SystemSettings\SystemSettingsCluster;
use App\Support\CrmMetrics;
use Filament\Actions\Action;
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

    protected function getHeaderActions(): array
    {
        return [
            Action::make('pipelines')
                ->label('管道设置')
                ->icon('heroicon-o-adjustments-horizontal')
                ->url(PipelineResource::getUrl()),
            Action::make('pipeline_stages')
                ->label('阶段设置')
                ->icon('heroicon-o-queue-list')
                ->url(PipelineStageResource::getUrl()),
            Action::make('pool_rules')
                ->label('公海规则')
                ->icon('heroicon-o-archive-box')
                ->url(CustomerPoolRuleResource::getUrl()),
            Action::make('assignment_rules')
                ->label('分配规则')
                ->icon('heroicon-o-users')
                ->url(AssignmentRuleResource::getUrl()),
            Action::make('score_rules')
                ->label('评分规则')
                ->icon('heroicon-o-star')
                ->url(LeadScoreRuleResource::getUrl()),
            Action::make('automation_rules')
                ->label('自动化规则')
                ->icon('heroicon-o-bolt')
                ->url(AutomationRuleResource::getUrl()),
        ];
    }
}

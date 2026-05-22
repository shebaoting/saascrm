<?php

namespace App\Filament\Clusters\SalesProcess\Pages;

use App\Filament\Clusters\SalesProcess\SalesProcessCluster;
use App\Models\Opportunity;
use App\Models\Pipeline;
use App\Models\PipelineStage;
use App\Services\Crm\OpportunityStageService;
use App\Support\CrmAccess;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Validation\ValidationException;
use Throwable;

class PipelineBoard extends Page
{
    protected string $view = 'filament.clusters.sales-process.pages.pipeline-board';

    protected static ?string $cluster = SalesProcessCluster::class;

    protected static ?string $navigationLabel = '商机看板';

    protected static ?string $title = '商机看板';

    protected static ?int $navigationSort = 1;

    public function moveToStage(int $opportunityId, int $stageId): void
    {
        try {
            $opportunity = Opportunity::query()->findOrFail($opportunityId);
            $stage = PipelineStage::query()->findOrFail($stageId);

            abort_unless(CrmAccess::recordIsVisible($opportunity), 403);

            app(OpportunityStageService::class)->move($opportunity, $stage, '看板拖拽推进');

            Notification::make()->success()->title('商机阶段已更新')->send();
        } catch (ValidationException $exception) {
            Notification::make()
                ->danger()
                ->title('无法推进阶段')
                ->body(collect($exception->errors())->flatten()->join("\n"))
                ->send();
        } catch (Throwable $exception) {
            report($exception);

            Notification::make()
                ->danger()
                ->title('阶段更新失败')
                ->body('页面已保留原阶段，请稍后重试。')
                ->send();
        }
    }

    protected function getViewData(): array
    {
        $tenantId = CrmAccess::tenantId();

        $pipeline = Pipeline::query()
            ->where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->orderByDesc('is_default')
            ->first();

        $stages = $pipeline
            ? $pipeline->stages()->where('is_active', true)->orderBy('sort_order')->get()
            : collect();

        $opportunities = Opportunity::query()
            ->where('tenant_id', $tenantId)
            ->whereNull('ended_at')
            ->with(['customer', 'responsible'])
            ->get()
            ->groupBy('pipeline_stage_id');

        return [
            'pipeline' => $pipeline,
            'stages' => $stages,
            'opportunities' => $opportunities,
        ];
    }
}

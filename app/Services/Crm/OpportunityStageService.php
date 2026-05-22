<?php

namespace App\Services\Crm;

use App\Models\Opportunity;
use App\Models\OpportunityStageHistory;
use App\Models\PipelineStage;
use App\Support\Filament\CustomFieldUi;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OpportunityStageService
{
    public function move(Opportunity $opportunity, PipelineStage $stage, ?string $notes = null): Opportunity
    {
        $this->assertCanMove($opportunity, $stage);

        return DB::transaction(function () use ($opportunity, $stage, $notes): Opportunity {
            $fromStageId = $opportunity->pipeline_stage_id;

            $opportunity->forceFill([
                'pipeline_id' => $stage->pipeline_id,
                'pipeline_stage_id' => $stage->id,
                'probability' => $stage->probability,
                'forecast_category' => $stage->stage_type === 'won' ? 'closed' : $opportunity->forecast_category,
                'closed_at' => $stage->stage_type === 'won' ? now() : $opportunity->closed_at,
                'ended_at' => in_array($stage->stage_type, ['won', 'lost', 'invalid'], true) ? now() : $opportunity->ended_at,
            ])->save();

            OpportunityStageHistory::create([
                'tenant_id' => $opportunity->tenant_id,
                'opportunity_id' => $opportunity->id,
                'from_stage_id' => $fromStageId,
                'to_stage_id' => $stage->id,
                'changed_by' => Auth::id() ?: $opportunity->responsible_user_id,
                'changed_at' => now(),
                'notes' => $notes,
            ]);

            app(AutomationService::class)->run('opportunity_stage_changed', $opportunity->refresh());

            return $opportunity->refresh();
        });
    }

    private function assertCanMove(Opportunity $opportunity, PipelineStage $stage): void
    {
        if ($opportunity->tenant_id !== $stage->tenant_id) {
            throw ValidationException::withMessages([
                'pipeline_stage_id' => '目标阶段不属于当前租户。',
            ]);
        }

        if ($opportunity->pipeline_id && $opportunity->pipeline_id !== $stage->pipeline_id) {
            throw ValidationException::withMessages([
                'pipeline_stage_id' => '目标阶段不属于当前商机的销售管道。',
            ]);
        }

        $missing = collect($stage->required_fields ?: [])
            ->filter(fn (string $field): bool => $this->requiredFieldIsMissing($opportunity, $field))
            ->map(fn (string $field): string => CustomFieldUi::fieldLabel('opportunity', $field))
            ->values()
            ->all();

        if ($missing !== []) {
            throw ValidationException::withMessages([
                'pipeline_stage_id' => '推进到「'.$stage->name.'」前请先填写：'.implode('、', $missing).'。',
            ]);
        }
    }

    private function requiredFieldIsMissing(Opportunity $opportunity, string $field): bool
    {
        $value = CustomFieldUi::fieldValue($opportunity, $field);

        if (in_array($field, ['amount', 'closed_amount'], true)) {
            return (float) $value <= 0;
        }

        return CustomFieldUi::valueIsBlank($value);
    }
}

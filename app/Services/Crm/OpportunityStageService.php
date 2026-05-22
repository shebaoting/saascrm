<?php

namespace App\Services\Crm;

use App\Models\Opportunity;
use App\Models\OpportunityStageHistory;
use App\Models\PipelineStage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OpportunityStageService
{
    public function move(Opportunity $opportunity, PipelineStage $stage, ?string $notes = null): Opportunity
    {
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

            return $opportunity->refresh();
        });
    }
}

<?php

namespace App\Filament\Clusters\SalesProcess\Resources\PipelineStages\Pages;

use App\Filament\Clusters\SalesProcess\Resources\PipelineStages\PipelineStageResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewPipelineStage extends ViewRecord
{
    protected static string $resource = PipelineStageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}

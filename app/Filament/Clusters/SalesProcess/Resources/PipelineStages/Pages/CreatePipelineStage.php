<?php

namespace App\Filament\Clusters\SalesProcess\Resources\PipelineStages\Pages;

use App\Filament\Clusters\SalesProcess\Resources\PipelineStages\PipelineStageResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePipelineStage extends CreateRecord
{
    protected static string $resource = PipelineStageResource::class;
}

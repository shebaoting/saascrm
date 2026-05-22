<?php

namespace App\Filament\Clusters\SalesProcess\Resources\PipelineStages\Pages;

use App\Filament\Clusters\SalesProcess\Resources\PipelineStages\PipelineStageResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditPipelineStage extends EditRecord
{
    protected static string $resource = PipelineStageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}

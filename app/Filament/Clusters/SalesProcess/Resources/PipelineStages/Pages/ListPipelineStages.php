<?php

namespace App\Filament\Clusters\SalesProcess\Resources\PipelineStages\Pages;

use App\Filament\Clusters\SalesProcess\Resources\PipelineStages\PipelineStageResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPipelineStages extends ListRecords
{
    protected static string $resource = PipelineStageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}

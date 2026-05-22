<?php

namespace App\Filament\Clusters\SalesProcess\Resources\Pipelines\Pages;

use App\Filament\Clusters\SalesProcess\Resources\Pipelines\PipelineResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewPipeline extends ViewRecord
{
    protected static string $resource = PipelineResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}

<?php

namespace App\Filament\Clusters\SalesProcess\Resources\Pipelines\Pages;

use App\Filament\Clusters\SalesProcess\Resources\Pipelines\PipelineResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditPipeline extends EditRecord
{
    protected static string $resource = PipelineResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}

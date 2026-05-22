<?php

namespace App\Filament\Clusters\SalesProcess\Resources\Pipelines\Pages;

use App\Filament\Clusters\SalesProcess\Resources\Pipelines\PipelineResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePipeline extends CreateRecord
{
    protected static string $resource = PipelineResource::class;
}

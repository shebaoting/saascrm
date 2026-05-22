<?php

namespace App\Filament\Clusters\SystemSettings\Resources\Exports\Pages;

use App\Filament\Clusters\SystemSettings\Resources\Exports\ExportResource;
use Filament\Resources\Pages\CreateRecord;

class CreateExport extends CreateRecord
{
    protected static string $resource = ExportResource::class;
}

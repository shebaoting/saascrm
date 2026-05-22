<?php

namespace App\Filament\Clusters\SystemSettings\Resources\Exports\Pages;

use App\Filament\Clusters\SystemSettings\Resources\Exports\ExportResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewExport extends ViewRecord
{
    protected static string $resource = ExportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}

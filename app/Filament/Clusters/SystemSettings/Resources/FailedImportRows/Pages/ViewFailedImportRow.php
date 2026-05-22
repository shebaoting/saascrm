<?php

namespace App\Filament\Clusters\SystemSettings\Resources\FailedImportRows\Pages;

use App\Filament\Clusters\SystemSettings\Resources\FailedImportRows\FailedImportRowResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewFailedImportRow extends ViewRecord
{
    protected static string $resource = FailedImportRowResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}

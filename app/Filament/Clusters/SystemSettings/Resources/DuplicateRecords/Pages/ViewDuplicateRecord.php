<?php

namespace App\Filament\Clusters\SystemSettings\Resources\DuplicateRecords\Pages;

use App\Filament\Clusters\SystemSettings\Resources\DuplicateRecords\DuplicateRecordResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewDuplicateRecord extends ViewRecord
{
    protected static string $resource = DuplicateRecordResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}

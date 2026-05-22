<?php

namespace App\Filament\Clusters\SystemSettings\Resources\Exports\Pages;

use App\Filament\Clusters\SystemSettings\Resources\Exports\ExportResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageExports extends ManageRecords
{
    protected static string $resource = ExportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}

<?php

namespace App\Filament\Clusters\SystemSettings\Resources\Imports\Pages;

use App\Filament\Clusters\SystemSettings\Resources\Imports\ImportResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageImports extends ManageRecords
{
    protected static string $resource = ImportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}

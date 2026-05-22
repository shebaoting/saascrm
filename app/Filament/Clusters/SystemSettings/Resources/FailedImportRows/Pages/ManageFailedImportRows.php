<?php

namespace App\Filament\Clusters\SystemSettings\Resources\FailedImportRows\Pages;

use App\Filament\Clusters\SystemSettings\Resources\FailedImportRows\FailedImportRowResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageFailedImportRows extends ManageRecords
{
    protected static string $resource = FailedImportRowResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}

<?php

namespace App\Filament\Clusters\SystemSettings\Resources\FailedImportRows\Pages;

use App\Filament\Clusters\SystemSettings\Resources\FailedImportRows\FailedImportRowResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditFailedImportRow extends EditRecord
{
    protected static string $resource = FailedImportRowResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}

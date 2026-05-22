<?php

namespace App\Filament\Clusters\SystemSettings\Resources\DuplicateRecords\Pages;

use App\Filament\Clusters\SystemSettings\Resources\DuplicateRecords\DuplicateRecordResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditDuplicateRecord extends EditRecord
{
    protected static string $resource = DuplicateRecordResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}

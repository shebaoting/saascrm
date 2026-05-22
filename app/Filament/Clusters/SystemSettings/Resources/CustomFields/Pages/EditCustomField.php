<?php

namespace App\Filament\Clusters\SystemSettings\Resources\CustomFields\Pages;

use App\Filament\Clusters\SystemSettings\Resources\CustomFields\CustomFieldResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditCustomField extends EditRecord
{
    protected static string $resource = CustomFieldResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}

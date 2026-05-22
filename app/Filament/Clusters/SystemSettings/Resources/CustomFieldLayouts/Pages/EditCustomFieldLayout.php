<?php

namespace App\Filament\Clusters\SystemSettings\Resources\CustomFieldLayouts\Pages;

use App\Filament\Clusters\SystemSettings\Resources\CustomFieldLayouts\CustomFieldLayoutResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditCustomFieldLayout extends EditRecord
{
    protected static string $resource = CustomFieldLayoutResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}

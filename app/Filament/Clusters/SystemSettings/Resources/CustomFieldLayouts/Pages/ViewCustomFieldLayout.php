<?php

namespace App\Filament\Clusters\SystemSettings\Resources\CustomFieldLayouts\Pages;

use App\Filament\Clusters\SystemSettings\Resources\CustomFieldLayouts\CustomFieldLayoutResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewCustomFieldLayout extends ViewRecord
{
    protected static string $resource = CustomFieldLayoutResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}

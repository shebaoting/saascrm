<?php

namespace App\Filament\Clusters\SystemSettings\Resources\CustomFields\Pages;

use App\Filament\Clusters\SystemSettings\Resources\CustomFields\CustomFieldResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewCustomField extends ViewRecord
{
    protected static string $resource = CustomFieldResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}

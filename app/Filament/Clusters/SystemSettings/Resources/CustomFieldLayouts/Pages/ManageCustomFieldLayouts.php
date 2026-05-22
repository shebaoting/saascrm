<?php

namespace App\Filament\Clusters\SystemSettings\Resources\CustomFieldLayouts\Pages;

use App\Filament\Clusters\SystemSettings\Resources\CustomFieldLayouts\CustomFieldLayoutResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageCustomFieldLayouts extends ManageRecords
{
    protected static string $resource = CustomFieldLayoutResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}

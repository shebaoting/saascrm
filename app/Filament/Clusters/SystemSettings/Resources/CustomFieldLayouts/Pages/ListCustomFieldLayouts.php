<?php

namespace App\Filament\Clusters\SystemSettings\Resources\CustomFieldLayouts\Pages;

use App\Filament\Clusters\SystemSettings\Resources\CustomFieldLayouts\CustomFieldLayoutResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCustomFieldLayouts extends ListRecords
{
    protected static string $resource = CustomFieldLayoutResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}

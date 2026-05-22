<?php

namespace App\Filament\Clusters\SystemSettings\Resources\CustomFields\Pages;

use App\Filament\Clusters\SystemSettings\Resources\CustomFields\CustomFieldResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageCustomFields extends ManageRecords
{
    protected static string $resource = CustomFieldResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}

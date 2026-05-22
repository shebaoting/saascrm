<?php

namespace App\Filament\Clusters\SystemSettings\Resources\CustomFieldLayouts\Pages;

use App\Filament\Clusters\SystemSettings\Resources\CustomFieldLayouts\CustomFieldLayoutResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCustomFieldLayout extends CreateRecord
{
    protected static string $resource = CustomFieldLayoutResource::class;
}

<?php

namespace App\Filament\Clusters\SystemSettings\Resources\CustomFields\Pages;

use App\Filament\Clusters\SystemSettings\Resources\CustomFields\CustomFieldResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCustomField extends CreateRecord
{
    protected static string $resource = CustomFieldResource::class;
}

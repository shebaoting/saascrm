<?php

namespace App\Filament\Clusters\SystemSettings\Resources\Imports\Pages;

use App\Filament\Clusters\SystemSettings\Resources\Imports\ImportResource;
use Filament\Resources\Pages\CreateRecord;

class CreateImport extends CreateRecord
{
    protected static string $resource = ImportResource::class;
}

<?php

namespace App\Filament\Clusters\SystemSettings\Resources\DuplicateRecords\Pages;

use App\Filament\Clusters\SystemSettings\Resources\DuplicateRecords\DuplicateRecordResource;
use Filament\Resources\Pages\CreateRecord;

class CreateDuplicateRecord extends CreateRecord
{
    protected static string $resource = DuplicateRecordResource::class;
}

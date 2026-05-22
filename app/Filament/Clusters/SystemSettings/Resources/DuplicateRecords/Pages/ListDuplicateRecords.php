<?php

namespace App\Filament\Clusters\SystemSettings\Resources\DuplicateRecords\Pages;

use App\Filament\Clusters\SystemSettings\Resources\DuplicateRecords\DuplicateRecordResource;
use Filament\Resources\Pages\ListRecords;

class ListDuplicateRecords extends ListRecords
{
    protected static string $resource = DuplicateRecordResource::class;
}

<?php

namespace App\Filament\Clusters\SystemSettings\Resources\FieldHistories\Pages;

use App\Filament\Clusters\SystemSettings\Resources\FieldHistories\FieldHistoryResource;
use Filament\Resources\Pages\ListRecords;

class ListFieldHistories extends ListRecords
{
    protected static string $resource = FieldHistoryResource::class;
}

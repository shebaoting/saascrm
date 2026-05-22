<?php

namespace App\Filament\Clusters\SystemSettings\Resources\FailedImportRows\Pages;

use App\Filament\Clusters\SystemSettings\Resources\FailedImportRows\FailedImportRowResource;
use Filament\Resources\Pages\CreateRecord;

class CreateFailedImportRow extends CreateRecord
{
    protected static string $resource = FailedImportRowResource::class;
}

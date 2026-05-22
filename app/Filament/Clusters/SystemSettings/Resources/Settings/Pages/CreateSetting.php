<?php

namespace App\Filament\Clusters\SystemSettings\Resources\Settings\Pages;

use App\Filament\Clusters\SystemSettings\Resources\Settings\SettingResource;
use Filament\Resources\Pages\CreateRecord;

class CreateSetting extends CreateRecord
{
    protected static string $resource = SettingResource::class;
}

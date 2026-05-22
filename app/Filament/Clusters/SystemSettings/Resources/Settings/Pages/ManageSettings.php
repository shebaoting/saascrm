<?php

namespace App\Filament\Clusters\SystemSettings\Resources\Settings\Pages;

use App\Filament\Clusters\SystemSettings\Resources\Settings\SettingResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageSettings extends ManageRecords
{
    protected static string $resource = SettingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}

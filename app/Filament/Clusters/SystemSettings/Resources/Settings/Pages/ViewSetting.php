<?php

namespace App\Filament\Clusters\SystemSettings\Resources\Settings\Pages;

use App\Filament\Clusters\SystemSettings\Resources\Settings\SettingResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewSetting extends ViewRecord
{
    protected static string $resource = SettingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}

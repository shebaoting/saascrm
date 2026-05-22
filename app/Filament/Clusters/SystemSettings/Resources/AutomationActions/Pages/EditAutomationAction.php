<?php

namespace App\Filament\Clusters\SystemSettings\Resources\AutomationActions\Pages;

use App\Filament\Clusters\SystemSettings\Resources\AutomationActions\AutomationActionResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditAutomationAction extends EditRecord
{
    protected static string $resource = AutomationActionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}

<?php

namespace App\Filament\Clusters\SystemSettings\Resources\AutomationActions\Pages;

use App\Filament\Clusters\SystemSettings\Resources\AutomationActions\AutomationActionResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewAutomationAction extends ViewRecord
{
    protected static string $resource = AutomationActionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}

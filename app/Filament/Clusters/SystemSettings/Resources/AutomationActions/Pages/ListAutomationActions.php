<?php

namespace App\Filament\Clusters\SystemSettings\Resources\AutomationActions\Pages;

use App\Filament\Clusters\SystemSettings\Resources\AutomationActions\AutomationActionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListAutomationActions extends ListRecords
{
    protected static string $resource = AutomationActionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}

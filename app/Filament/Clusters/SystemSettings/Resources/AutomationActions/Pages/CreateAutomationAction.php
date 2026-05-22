<?php

namespace App\Filament\Clusters\SystemSettings\Resources\AutomationActions\Pages;

use App\Filament\Clusters\SystemSettings\Resources\AutomationActions\AutomationActionResource;
use Filament\Resources\Pages\CreateRecord;

class CreateAutomationAction extends CreateRecord
{
    protected static string $resource = AutomationActionResource::class;
}

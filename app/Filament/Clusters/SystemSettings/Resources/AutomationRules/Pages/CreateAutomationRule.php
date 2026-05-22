<?php

namespace App\Filament\Clusters\SystemSettings\Resources\AutomationRules\Pages;

use App\Filament\Clusters\SystemSettings\Resources\AutomationRules\AutomationRuleResource;
use Filament\Resources\Pages\CreateRecord;

class CreateAutomationRule extends CreateRecord
{
    protected static string $resource = AutomationRuleResource::class;
}

<?php

namespace App\Filament\Clusters\SystemSettings\Resources\AutomationRules\Pages;

use App\Filament\Clusters\SystemSettings\Resources\AutomationRules\AutomationRuleResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewAutomationRule extends ViewRecord
{
    protected static string $resource = AutomationRuleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}

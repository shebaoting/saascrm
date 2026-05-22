<?php

namespace App\Filament\Clusters\LeadCenter\Resources\AssignmentRules\Pages;

use App\Filament\Clusters\LeadCenter\Resources\AssignmentRules\AssignmentRuleResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewAssignmentRule extends ViewRecord
{
    protected static string $resource = AssignmentRuleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}

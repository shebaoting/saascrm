<?php

namespace App\Filament\Clusters\LeadCenter\Resources\AssignmentRules\Pages;

use App\Filament\Clusters\LeadCenter\Resources\AssignmentRules\AssignmentRuleResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageAssignmentRules extends ManageRecords
{
    protected static string $resource = AssignmentRuleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}

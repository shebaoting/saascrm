<?php

namespace App\Filament\Clusters\LeadCenter\Resources\AssignmentRules\Pages;

use App\Filament\Clusters\LeadCenter\Resources\AssignmentRules\AssignmentRuleResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditAssignmentRule extends EditRecord
{
    protected static string $resource = AssignmentRuleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}

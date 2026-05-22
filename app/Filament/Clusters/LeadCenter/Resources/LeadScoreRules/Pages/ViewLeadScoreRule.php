<?php

namespace App\Filament\Clusters\LeadCenter\Resources\LeadScoreRules\Pages;

use App\Filament\Clusters\LeadCenter\Resources\LeadScoreRules\LeadScoreRuleResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewLeadScoreRule extends ViewRecord
{
    protected static string $resource = LeadScoreRuleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}

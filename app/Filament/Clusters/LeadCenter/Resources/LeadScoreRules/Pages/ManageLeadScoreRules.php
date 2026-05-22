<?php

namespace App\Filament\Clusters\LeadCenter\Resources\LeadScoreRules\Pages;

use App\Filament\Clusters\LeadCenter\Resources\LeadScoreRules\LeadScoreRuleResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageLeadScoreRules extends ManageRecords
{
    protected static string $resource = LeadScoreRuleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}

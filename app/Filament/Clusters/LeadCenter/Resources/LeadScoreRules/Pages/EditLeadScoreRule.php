<?php

namespace App\Filament\Clusters\LeadCenter\Resources\LeadScoreRules\Pages;

use App\Filament\Clusters\LeadCenter\Resources\LeadScoreRules\LeadScoreRuleResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditLeadScoreRule extends EditRecord
{
    protected static string $resource = LeadScoreRuleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}

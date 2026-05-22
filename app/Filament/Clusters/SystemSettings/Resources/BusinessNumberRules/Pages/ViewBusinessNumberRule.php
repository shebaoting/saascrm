<?php

namespace App\Filament\Clusters\SystemSettings\Resources\BusinessNumberRules\Pages;

use App\Filament\Clusters\SystemSettings\Resources\BusinessNumberRules\BusinessNumberRuleResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewBusinessNumberRule extends ViewRecord
{
    protected static string $resource = BusinessNumberRuleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}

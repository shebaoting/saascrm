<?php

namespace App\Filament\Clusters\SystemSettings\Resources\BusinessNumberRules\Pages;

use App\Filament\Clusters\SystemSettings\Resources\BusinessNumberRules\BusinessNumberRuleResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageBusinessNumberRules extends ManageRecords
{
    protected static string $resource = BusinessNumberRuleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}

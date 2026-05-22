<?php

namespace App\Filament\Clusters\CustomerCenter\Resources\CustomerPoolRules\Pages;

use App\Filament\Clusters\CustomerCenter\Resources\CustomerPoolRules\CustomerPoolRuleResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageCustomerPoolRules extends ManageRecords
{
    protected static string $resource = CustomerPoolRuleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}

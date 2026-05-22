<?php

namespace App\Filament\Clusters\CustomerCenter\Resources\CustomerPoolRules\Pages;

use App\Filament\Clusters\CustomerCenter\Resources\CustomerPoolRules\CustomerPoolRuleResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewCustomerPoolRule extends ViewRecord
{
    protected static string $resource = CustomerPoolRuleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}

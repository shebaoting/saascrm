<?php

namespace App\Filament\Clusters\CustomerCenter\Resources\CustomerPoolRules\Pages;

use App\Filament\Clusters\CustomerCenter\Resources\CustomerPoolRules\CustomerPoolRuleResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCustomerPoolRule extends CreateRecord
{
    protected static string $resource = CustomerPoolRuleResource::class;
}

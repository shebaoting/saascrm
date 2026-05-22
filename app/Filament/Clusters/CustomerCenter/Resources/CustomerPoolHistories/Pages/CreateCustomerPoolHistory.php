<?php

namespace App\Filament\Clusters\CustomerCenter\Resources\CustomerPoolHistories\Pages;

use App\Filament\Clusters\CustomerCenter\Resources\CustomerPoolHistories\CustomerPoolHistoryResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCustomerPoolHistory extends CreateRecord
{
    protected static string $resource = CustomerPoolHistoryResource::class;
}

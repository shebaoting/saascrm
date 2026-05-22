<?php

namespace App\Filament\Clusters\CustomerCenter\Resources\Customers\Pages;

use App\Filament\Clusters\CustomerCenter\Resources\Customers\CustomerResource;
use Filament\Resources\Pages\ListRecords;

class ListCustomers extends ListRecords
{
    protected static string $resource = CustomerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CustomerResource::makeCreateAction(),
        ];
    }
}

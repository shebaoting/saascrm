<?php

namespace App\Filament\Clusters\CustomerCenter\Resources\MyCustomers\Pages;

use App\Filament\Clusters\CustomerCenter\Resources\MyCustomers\MyCustomerResource;
use Filament\Resources\Pages\ListRecords;

class ListMyCustomers extends ListRecords
{
    protected static string $resource = MyCustomerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            MyCustomerResource::makeCreateAction(),
        ];
    }
}

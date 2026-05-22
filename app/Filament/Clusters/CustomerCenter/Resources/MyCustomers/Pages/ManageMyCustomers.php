<?php

namespace App\Filament\Clusters\CustomerCenter\Resources\MyCustomers\Pages;

use App\Filament\Clusters\CustomerCenter\Resources\MyCustomers\MyCustomerResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageMyCustomers extends ManageRecords
{
    protected static string $resource = MyCustomerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}

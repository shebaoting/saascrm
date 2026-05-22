<?php

namespace App\Filament\Clusters\CustomerCenter\Resources\Customers\Pages;

use App\Filament\Clusters\CustomerCenter\Resources\Customers\CustomerResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageCustomers extends ManageRecords
{
    protected static string $resource = CustomerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}

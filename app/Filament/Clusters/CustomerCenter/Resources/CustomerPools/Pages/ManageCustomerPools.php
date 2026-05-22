<?php

namespace App\Filament\Clusters\CustomerCenter\Resources\CustomerPools\Pages;

use App\Filament\Clusters\CustomerCenter\Resources\CustomerPools\CustomerPoolResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageCustomerPools extends ManageRecords
{
    protected static string $resource = CustomerPoolResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}

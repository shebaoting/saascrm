<?php

namespace App\Filament\Clusters\CustomerCenter\Resources\MyCustomers\Pages;

use App\Filament\Clusters\CustomerCenter\Resources\MyCustomers\MyCustomerResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditMyCustomer extends EditRecord
{
    protected static string $resource = MyCustomerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}

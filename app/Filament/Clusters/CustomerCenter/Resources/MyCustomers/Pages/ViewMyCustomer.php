<?php

namespace App\Filament\Clusters\CustomerCenter\Resources\MyCustomers\Pages;

use App\Filament\Clusters\CustomerCenter\Resources\Customers\Pages\Concerns\HasCustomerViewActions;
use App\Filament\Clusters\CustomerCenter\Resources\MyCustomers\MyCustomerResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewMyCustomer extends ViewRecord
{
    use HasCustomerViewActions;

    protected static string $resource = MyCustomerResource::class;

    protected static ?string $title = '客户详情';

    protected function getHeaderActions(): array
    {
        return [
            ...$this->getCustomerViewActions(),
            EditAction::make(),
        ];
    }
}

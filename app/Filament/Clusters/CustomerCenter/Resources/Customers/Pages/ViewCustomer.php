<?php

namespace App\Filament\Clusters\CustomerCenter\Resources\Customers\Pages;

use App\Filament\Clusters\CustomerCenter\Resources\Customers\CustomerResource;
use App\Filament\Clusters\CustomerCenter\Resources\Customers\Pages\Concerns\HasCustomerViewActions;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewCustomer extends ViewRecord
{
    use HasCustomerViewActions;

    protected static string $resource = CustomerResource::class;

    protected static ?string $title = '客户详情';

    protected function getHeaderActions(): array
    {
        return [
            ...$this->getCustomerViewActions(),
            EditAction::make(),
        ];
    }
}

<?php

namespace App\Filament\Clusters\CustomerCenter\Resources\CustomerPools\Pages;

use App\Filament\Clusters\CustomerCenter\Resources\CustomerPools\CustomerPoolResource;
use App\Filament\Clusters\CustomerCenter\Resources\Customers\Pages\Concerns\HasCustomerViewActions;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewCustomerPool extends ViewRecord
{
    use HasCustomerViewActions;

    protected static string $resource = CustomerPoolResource::class;

    protected static ?string $title = '客户详情';

    protected function getHeaderActions(): array
    {
        return [
            ...$this->getCustomerViewActions(),
            EditAction::make(),
        ];
    }
}

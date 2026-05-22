<?php

namespace App\Filament\Clusters\CustomerCenter\Resources\CustomerPools\Pages;

use App\Filament\Clusters\CustomerCenter\Resources\CustomerPools\CustomerPoolResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditCustomerPool extends EditRecord
{
    protected static string $resource = CustomerPoolResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}

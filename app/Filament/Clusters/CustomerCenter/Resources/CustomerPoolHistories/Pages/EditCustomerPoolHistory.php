<?php

namespace App\Filament\Clusters\CustomerCenter\Resources\CustomerPoolHistories\Pages;

use App\Filament\Clusters\CustomerCenter\Resources\CustomerPoolHistories\CustomerPoolHistoryResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditCustomerPoolHistory extends EditRecord
{
    protected static string $resource = CustomerPoolHistoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}

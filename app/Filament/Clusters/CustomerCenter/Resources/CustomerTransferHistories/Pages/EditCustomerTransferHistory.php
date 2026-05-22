<?php

namespace App\Filament\Clusters\CustomerCenter\Resources\CustomerTransferHistories\Pages;

use App\Filament\Clusters\CustomerCenter\Resources\CustomerTransferHistories\CustomerTransferHistoryResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditCustomerTransferHistory extends EditRecord
{
    protected static string $resource = CustomerTransferHistoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}

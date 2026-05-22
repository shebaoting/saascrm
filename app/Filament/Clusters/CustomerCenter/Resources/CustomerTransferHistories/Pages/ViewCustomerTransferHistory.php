<?php

namespace App\Filament\Clusters\CustomerCenter\Resources\CustomerTransferHistories\Pages;

use App\Filament\Clusters\CustomerCenter\Resources\CustomerTransferHistories\CustomerTransferHistoryResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewCustomerTransferHistory extends ViewRecord
{
    protected static string $resource = CustomerTransferHistoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}

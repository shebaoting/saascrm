<?php

namespace App\Filament\Clusters\CustomerCenter\Resources\CustomerTransferHistories\Pages;

use App\Filament\Clusters\CustomerCenter\Resources\CustomerTransferHistories\CustomerTransferHistoryResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCustomerTransferHistories extends ListRecords
{
    protected static string $resource = CustomerTransferHistoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}

<?php

namespace App\Filament\Clusters\CustomerCenter\Resources\CustomerPoolHistories\Pages;

use App\Filament\Clusters\CustomerCenter\Resources\CustomerPoolHistories\CustomerPoolHistoryResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageCustomerPoolHistories extends ManageRecords
{
    protected static string $resource = CustomerPoolHistoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}

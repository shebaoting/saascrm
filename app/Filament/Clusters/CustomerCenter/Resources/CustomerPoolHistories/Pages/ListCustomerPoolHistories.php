<?php

namespace App\Filament\Clusters\CustomerCenter\Resources\CustomerPoolHistories\Pages;

use App\Filament\Clusters\CustomerCenter\Resources\CustomerPoolHistories\CustomerPoolHistoryResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCustomerPoolHistories extends ListRecords
{
    protected static string $resource = CustomerPoolHistoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}

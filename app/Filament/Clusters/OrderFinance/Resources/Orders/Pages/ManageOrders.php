<?php

namespace App\Filament\Clusters\OrderFinance\Resources\Orders\Pages;

use App\Filament\Clusters\OrderFinance\Resources\Orders\OrderResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageOrders extends ManageRecords
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}

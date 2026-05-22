<?php

namespace App\Filament\Clusters\OrderFinance\Resources\OrderExpenses\Pages;

use App\Filament\Clusters\OrderFinance\Resources\OrderExpenses\OrderExpenseResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListOrderExpenses extends ListRecords
{
    protected static string $resource = OrderExpenseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}

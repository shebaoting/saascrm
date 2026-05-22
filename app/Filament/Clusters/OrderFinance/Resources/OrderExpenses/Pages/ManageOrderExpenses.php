<?php

namespace App\Filament\Clusters\OrderFinance\Resources\OrderExpenses\Pages;

use App\Filament\Clusters\OrderFinance\Resources\OrderExpenses\OrderExpenseResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageOrderExpenses extends ManageRecords
{
    protected static string $resource = OrderExpenseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}

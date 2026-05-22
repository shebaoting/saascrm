<?php

namespace App\Filament\Clusters\OrderFinance\Resources\OrderExpenses\Pages;

use App\Filament\Clusters\OrderFinance\Resources\OrderExpenses\OrderExpenseResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewOrderExpense extends ViewRecord
{
    protected static string $resource = OrderExpenseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}

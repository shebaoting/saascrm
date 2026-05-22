<?php

namespace App\Filament\Clusters\OrderFinance\Resources\OrderExpenses\Pages;

use App\Filament\Clusters\OrderFinance\Resources\OrderExpenses\OrderExpenseResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditOrderExpense extends EditRecord
{
    protected static string $resource = OrderExpenseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}

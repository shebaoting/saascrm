<?php

namespace App\Filament\Clusters\OrderFinance\Resources\OrderExpenses\Pages;

use App\Filament\Clusters\OrderFinance\Resources\OrderExpenses\OrderExpenseResource;
use Filament\Resources\Pages\CreateRecord;

class CreateOrderExpense extends CreateRecord
{
    protected static string $resource = OrderExpenseResource::class;
}

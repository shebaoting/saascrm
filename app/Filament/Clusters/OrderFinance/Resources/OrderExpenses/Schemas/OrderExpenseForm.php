<?php

namespace App\Filament\Clusters\OrderFinance\Resources\OrderExpenses\Schemas;

use App\Support\Filament\CrmUi;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class OrderExpenseForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('order_id')
                    ->relationship('order', 'order_number')
                    ->required(),
                DatePicker::make('expense_date')
                    ->required(),
                TextInput::make('amount')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('category'),
                TextInput::make('reason')
                    ->required(),
                Select::make('status')
                    ->options(CrmUi::options('order_expense.status'))
                    ->required()
                    ->default('pending'),
            ]);
    }
}

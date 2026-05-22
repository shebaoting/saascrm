<?php

namespace App\Filament\Clusters\OrderFinance\Resources\OrderExpenses\Schemas;

use App\Models\OrderExpense;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class OrderExpenseInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('deleted_at')
                    ->dateTime()
                    ->visible(fn (OrderExpense $record): bool => $record->trashed()),
                TextEntry::make('order.order_number')
                    ->label('订单'),
                TextEntry::make('expense_date')
                    ->date(),
                TextEntry::make('amount')
                    ->numeric(),
                TextEntry::make('category')
                    ->placeholder('-'),
                TextEntry::make('reason'),
                TextEntry::make('status'),
                TextEntry::make('approvedBy.name')
                    ->placeholder('-'),
                TextEntry::make('approved_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}

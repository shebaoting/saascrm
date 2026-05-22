<?php

namespace App\Filament\Clusters\OrderFinance\Resources\Payments\Schemas;

use App\Models\Payment;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class PaymentInfolist
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
                    ->visible(fn (Payment $record): bool => $record->trashed()),
                TextEntry::make('order.order_number')
                    ->label('订单'),
                TextEntry::make('paymentPlan.plan_date')
                    ->label('收款计划')
                    ->date()
                    ->placeholder('-'),
                TextEntry::make('plan_date')
                    ->date()
                    ->placeholder('-'),
                TextEntry::make('received_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('amount')
                    ->numeric(),
                TextEntry::make('status'),
                TextEntry::make('payment_method')
                    ->placeholder('-'),
                TextEntry::make('transaction_no')
                    ->placeholder('-'),
                TextEntry::make('notes')
                    ->placeholder('-'),
            ]);
    }
}

<?php

namespace App\Filament\Clusters\OrderFinance\Resources\Orders\Schemas;

use App\Models\Order;
use App\Support\Filament\CrmUi;
use App\Support\Filament\CustomFieldUi;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class OrderInfolist
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
                    ->visible(fn (Order $record): bool => $record->trashed()),
                TextEntry::make('order_number'),
                TextEntry::make('customer.name')
                    ->label('客户'),
                TextEntry::make('contact.name')
                    ->label('联系人')
                    ->placeholder('-'),
                TextEntry::make('opportunity.name')
                    ->label('商机')
                    ->placeholder('-'),
                TextEntry::make('quote.title')
                    ->label('报价单')
                    ->placeholder('-'),
                TextEntry::make('employee.name')
                    ->placeholder('-'),
                TextEntry::make('subtotal_amount')
                    ->numeric(),
                TextEntry::make('discount_amount')
                    ->numeric(),
                TextEntry::make('total_amount')
                    ->numeric(),
                TextEntry::make('total_cost')
                    ->money(),
                TextEntry::make('gross_profit')
                    ->numeric(),
                TextEntry::make('order_source'),
                TextEntry::make('order_status'),
                TextEntry::make('payment_status'),
                RepeatableEntry::make('paymentPlans')
                    ->label('收款计划')
                    ->schema([
                        TextEntry::make('plan_date')
                            ->date(),
                        TextEntry::make('plan_amount')
                            ->money(),
                        TextEntry::make('received_amount')
                            ->money(),
                        TextEntry::make('status'),
                        TextEntry::make('notes')
                            ->placeholder('-'),
                    ])
                    ->columns(5)
                    ->columnSpanFull(),
                RepeatableEntry::make('attachments')
                    ->label('合同和凭证')
                    ->schema([
                        TextEntry::make('category')
                            ->formatStateUsing(fn (mixed $state): ?string => CrmUi::options('attachment.category')[$state] ?? $state),
                        TextEntry::make('name')
                            ->placeholder('-'),
                        TextEntry::make('path')
                            ->placeholder('-'),
                    ])
                    ->columns(3)
                    ->columnSpanFull(),
                TextEntry::make('ordered_at')
                    ->dateTime(),
                TextEntry::make('completed_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('notes')
                    ->placeholder('-')
                    ->columnSpanFull(),
                ...CustomFieldUi::infolistSections('order'),
            ]);
    }
}

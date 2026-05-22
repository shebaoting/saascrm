<?php

namespace App\Filament\Clusters\OrderFinance\Resources\OrderItems\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class OrderItemInfolist
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
                TextEntry::make('order.order_number')
                    ->label('订单'),
                TextEntry::make('product.name'),
                TextEntry::make('sku.sku_code')
                    ->placeholder('-'),
                TextEntry::make('product_name'),
                TextEntry::make('sku_code')
                    ->placeholder('-'),
                TextEntry::make('quantity')
                    ->numeric(),
                TextEntry::make('unit_price')
                    ->money(),
                TextEntry::make('cost_price')
                    ->money(),
                TextEntry::make('tax_rate')
                    ->numeric(),
                TextEntry::make('subtotal_amount')
                    ->numeric(),
            ]);
    }
}

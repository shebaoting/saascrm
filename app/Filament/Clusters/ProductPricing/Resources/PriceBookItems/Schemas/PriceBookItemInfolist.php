<?php

namespace App\Filament\Clusters\ProductPricing\Resources\PriceBookItems\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class PriceBookItemInfolist
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
                TextEntry::make('priceBook.name')
                    ->label('价格表'),
                TextEntry::make('sku.sku_code'),
                TextEntry::make('price')
                    ->money(),
                TextEntry::make('min_price')
                    ->money()
                    ->placeholder('-'),
                TextEntry::make('starts_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('ends_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}

<?php

namespace App\Filament\Clusters\ProductPricing\Resources\ProductSkus\Schemas;

use App\Models\ProductSku;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class ProductSkuInfolist
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
                    ->visible(fn (ProductSku $record): bool => $record->trashed()),
                TextEntry::make('product.name')
                    ->label('商品'),
                TextEntry::make('sku_code'),
                TextEntry::make('price')
                    ->money(),
                TextEntry::make('cost_price')
                    ->money(),
                TextEntry::make('original_price')
                    ->money()
                    ->placeholder('-'),
                TextEntry::make('stock')
                    ->numeric(),
                IconEntry::make('is_active')
                    ->boolean(),
            ]);
    }
}

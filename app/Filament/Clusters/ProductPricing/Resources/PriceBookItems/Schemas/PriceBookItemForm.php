<?php

namespace App\Filament\Clusters\ProductPricing\Resources\PriceBookItems\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class PriceBookItemForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('price_book_id')
                    ->relationship('priceBook', 'name')
                    ->required(),
                Select::make('product_sku_id')
                    ->relationship('sku', 'sku_code')
                    ->required(),
                TextInput::make('price')
                    ->required()
                    ->numeric()
                    ->default(0)
                    ->prefix('$'),
                TextInput::make('min_price')
                    ->numeric()
                    ->prefix('$'),
                DateTimePicker::make('starts_at'),
                DateTimePicker::make('ends_at'),
            ]);
    }
}

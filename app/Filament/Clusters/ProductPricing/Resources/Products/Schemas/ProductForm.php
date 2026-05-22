<?php

namespace App\Filament\Clusters\ProductPricing\Resources\Products\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                Select::make('group_id')
                    ->relationship('group', 'name'),
                TextInput::make('tax_rate')
                    ->required()
                    ->numeric()
                    ->default(0),
                Textarea::make('details')
                    ->columnSpanFull(),
                Toggle::make('is_on_sale')
                    ->required(),
                Textarea::make('internal_notes')
                    ->columnSpanFull(),
            ]);
    }
}

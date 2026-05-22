<?php

namespace App\Filament\Clusters\ProductPricing\Resources\ProductGroups\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ProductGroupForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                TextInput::make('sort_order')
                    ->required()
                    ->numeric()
                    ->default(0),
            ]);
    }
}

<?php

namespace App\Filament\Clusters\ProductPricing\Resources\Products\Schemas;

use App\Models\Product;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class ProductInfolist
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
                    ->visible(fn (Product $record): bool => $record->trashed()),
                TextEntry::make('name'),
                TextEntry::make('group.name')
                    ->label('分组')
                    ->placeholder('-'),
                TextEntry::make('tax_rate')
                    ->numeric(),
                TextEntry::make('details')
                    ->placeholder('-')
                    ->columnSpanFull(),
                IconEntry::make('is_on_sale')
                    ->boolean(),
                TextEntry::make('internal_notes')
                    ->placeholder('-')
                    ->columnSpanFull(),
            ]);
    }
}

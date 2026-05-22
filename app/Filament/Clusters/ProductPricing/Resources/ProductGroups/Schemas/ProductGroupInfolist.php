<?php

namespace App\Filament\Clusters\ProductPricing\Resources\ProductGroups\Schemas;

use App\Models\ProductGroup;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class ProductGroupInfolist
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
                    ->visible(fn (ProductGroup $record): bool => $record->trashed()),
                TextEntry::make('name'),
                TextEntry::make('sort_order')
                    ->numeric(),
            ]);
    }
}

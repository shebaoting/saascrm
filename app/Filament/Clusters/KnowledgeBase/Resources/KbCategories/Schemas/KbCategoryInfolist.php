<?php

namespace App\Filament\Clusters\KnowledgeBase\Resources\KbCategories\Schemas;

use App\Models\KbCategory;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class KbCategoryInfolist
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
                    ->visible(fn (KbCategory $record): bool => $record->trashed()),
                TextEntry::make('name'),
                TextEntry::make('parent.name')
                    ->label('上级')
                    ->placeholder('-'),
                TextEntry::make('sort_order')
                    ->numeric(),
            ]);
    }
}

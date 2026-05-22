<?php

namespace App\Filament\Clusters\KnowledgeBase\Resources\KbArticles\Schemas;

use App\Models\KbArticle;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class KbArticleInfolist
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
                    ->visible(fn (KbArticle $record): bool => $record->trashed()),
                TextEntry::make('title'),
                TextEntry::make('content')
                    ->columnSpanFull(),
                TextEntry::make('category.name')
                    ->placeholder('-'),
                TextEntry::make('author.name')
                    ->placeholder('-'),
                TextEntry::make('status'),
                TextEntry::make('published_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}

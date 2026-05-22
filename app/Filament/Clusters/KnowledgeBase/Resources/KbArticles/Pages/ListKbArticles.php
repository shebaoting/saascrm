<?php

namespace App\Filament\Clusters\KnowledgeBase\Resources\KbArticles\Pages;

use App\Filament\Clusters\KnowledgeBase\Resources\KbArticles\KbArticleResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListKbArticles extends ListRecords
{
    protected static string $resource = KbArticleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}

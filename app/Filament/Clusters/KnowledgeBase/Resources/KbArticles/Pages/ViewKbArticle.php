<?php

namespace App\Filament\Clusters\KnowledgeBase\Resources\KbArticles\Pages;

use App\Filament\Clusters\KnowledgeBase\Resources\KbArticles\KbArticleResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewKbArticle extends ViewRecord
{
    protected static string $resource = KbArticleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}

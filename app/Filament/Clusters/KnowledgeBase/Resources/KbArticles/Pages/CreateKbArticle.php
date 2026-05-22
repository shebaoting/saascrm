<?php

namespace App\Filament\Clusters\KnowledgeBase\Resources\KbArticles\Pages;

use App\Filament\Clusters\KnowledgeBase\Resources\KbArticles\KbArticleResource;
use Filament\Resources\Pages\CreateRecord;

class CreateKbArticle extends CreateRecord
{
    protected static string $resource = KbArticleResource::class;
}

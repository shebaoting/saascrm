<?php

namespace App\Filament\Clusters\KnowledgeBase\Resources\KbCategories\Pages;

use App\Filament\Clusters\KnowledgeBase\Resources\KbCategories\KbCategoryResource;
use Filament\Resources\Pages\CreateRecord;

class CreateKbCategory extends CreateRecord
{
    protected static string $resource = KbCategoryResource::class;
}

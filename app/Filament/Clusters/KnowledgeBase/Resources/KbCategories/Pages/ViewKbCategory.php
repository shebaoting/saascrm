<?php

namespace App\Filament\Clusters\KnowledgeBase\Resources\KbCategories\Pages;

use App\Filament\Clusters\KnowledgeBase\Resources\KbCategories\KbCategoryResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewKbCategory extends ViewRecord
{
    protected static string $resource = KbCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}

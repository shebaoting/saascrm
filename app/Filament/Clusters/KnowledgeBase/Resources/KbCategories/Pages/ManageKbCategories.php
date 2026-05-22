<?php

namespace App\Filament\Clusters\KnowledgeBase\Resources\KbCategories\Pages;

use App\Filament\Clusters\KnowledgeBase\Resources\KbCategories\KbCategoryResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageKbCategories extends ManageRecords
{
    protected static string $resource = KbCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}

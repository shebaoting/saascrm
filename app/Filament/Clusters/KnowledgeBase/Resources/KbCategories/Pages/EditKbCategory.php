<?php

namespace App\Filament\Clusters\KnowledgeBase\Resources\KbCategories\Pages;

use App\Filament\Clusters\KnowledgeBase\Resources\KbCategories\KbCategoryResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditKbCategory extends EditRecord
{
    protected static string $resource = KbCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}

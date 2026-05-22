<?php

namespace App\Filament\Clusters\ProductPricing\Resources\ProductGroups\Pages;

use App\Filament\Clusters\ProductPricing\Resources\ProductGroups\ProductGroupResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditProductGroup extends EditRecord
{
    protected static string $resource = ProductGroupResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}

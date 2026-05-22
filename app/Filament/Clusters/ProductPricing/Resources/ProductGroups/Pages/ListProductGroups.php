<?php

namespace App\Filament\Clusters\ProductPricing\Resources\ProductGroups\Pages;

use App\Filament\Clusters\ProductPricing\Resources\ProductGroups\ProductGroupResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListProductGroups extends ListRecords
{
    protected static string $resource = ProductGroupResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}

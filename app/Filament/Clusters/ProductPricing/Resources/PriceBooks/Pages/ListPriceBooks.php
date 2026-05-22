<?php

namespace App\Filament\Clusters\ProductPricing\Resources\PriceBooks\Pages;

use App\Filament\Clusters\ProductPricing\Resources\PriceBooks\PriceBookResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPriceBooks extends ListRecords
{
    protected static string $resource = PriceBookResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}

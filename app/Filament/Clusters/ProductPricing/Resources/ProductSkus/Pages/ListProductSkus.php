<?php

namespace App\Filament\Clusters\ProductPricing\Resources\ProductSkus\Pages;

use App\Filament\Clusters\ProductPricing\Resources\ProductSkus\ProductSkuResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListProductSkus extends ListRecords
{
    protected static string $resource = ProductSkuResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}

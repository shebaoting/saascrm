<?php

namespace App\Filament\Clusters\ProductPricing\Resources\ProductSkus\Pages;

use App\Filament\Clusters\ProductPricing\Resources\ProductSkus\ProductSkuResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageProductSkus extends ManageRecords
{
    protected static string $resource = ProductSkuResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}

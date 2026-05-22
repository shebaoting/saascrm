<?php

namespace App\Filament\Clusters\ProductPricing\Resources\ProductSkus\Pages;

use App\Filament\Clusters\ProductPricing\Resources\ProductSkus\ProductSkuResource;
use Filament\Resources\Pages\CreateRecord;

class CreateProductSku extends CreateRecord
{
    protected static string $resource = ProductSkuResource::class;
}

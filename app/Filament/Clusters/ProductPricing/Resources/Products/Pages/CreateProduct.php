<?php

namespace App\Filament\Clusters\ProductPricing\Resources\Products\Pages;

use App\Filament\Clusters\ProductPricing\Resources\Products\ProductResource;
use Filament\Resources\Pages\CreateRecord;

class CreateProduct extends CreateRecord
{
    protected static string $resource = ProductResource::class;
}

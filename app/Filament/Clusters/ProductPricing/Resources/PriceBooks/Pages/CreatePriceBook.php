<?php

namespace App\Filament\Clusters\ProductPricing\Resources\PriceBooks\Pages;

use App\Filament\Clusters\ProductPricing\Resources\PriceBooks\PriceBookResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePriceBook extends CreateRecord
{
    protected static string $resource = PriceBookResource::class;
}

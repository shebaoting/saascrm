<?php

namespace App\Filament\Clusters\ProductPricing\Resources\PriceBookItems\Pages;

use App\Filament\Clusters\ProductPricing\Resources\PriceBookItems\PriceBookItemResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePriceBookItem extends CreateRecord
{
    protected static string $resource = PriceBookItemResource::class;
}

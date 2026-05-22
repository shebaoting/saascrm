<?php

namespace App\Filament\Clusters\ProductPricing\Resources\ProductGroups\Pages;

use App\Filament\Clusters\ProductPricing\Resources\ProductGroups\ProductGroupResource;
use Filament\Resources\Pages\CreateRecord;

class CreateProductGroup extends CreateRecord
{
    protected static string $resource = ProductGroupResource::class;
}

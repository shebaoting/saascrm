<?php

namespace App\Filament\Clusters\ProductPricing\Resources\ProductSkus\Pages;

use App\Filament\Clusters\ProductPricing\Resources\ProductSkus\ProductSkuResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewProductSku extends ViewRecord
{
    protected static string $resource = ProductSkuResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}

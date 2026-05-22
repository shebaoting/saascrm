<?php

namespace App\Filament\Clusters\ProductPricing\Resources\ProductSkus\Pages;

use App\Filament\Clusters\ProductPricing\Resources\ProductSkus\ProductSkuResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditProductSku extends EditRecord
{
    protected static string $resource = ProductSkuResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}

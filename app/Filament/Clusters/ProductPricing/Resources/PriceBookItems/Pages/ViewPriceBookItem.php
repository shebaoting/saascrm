<?php

namespace App\Filament\Clusters\ProductPricing\Resources\PriceBookItems\Pages;

use App\Filament\Clusters\ProductPricing\Resources\PriceBookItems\PriceBookItemResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewPriceBookItem extends ViewRecord
{
    protected static string $resource = PriceBookItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}

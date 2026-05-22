<?php

namespace App\Filament\Clusters\ProductPricing\Resources\PriceBooks\Pages;

use App\Filament\Clusters\ProductPricing\Resources\PriceBooks\PriceBookResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewPriceBook extends ViewRecord
{
    protected static string $resource = PriceBookResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}

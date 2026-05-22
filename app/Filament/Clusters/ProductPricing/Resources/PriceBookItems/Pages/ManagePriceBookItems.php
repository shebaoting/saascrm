<?php

namespace App\Filament\Clusters\ProductPricing\Resources\PriceBookItems\Pages;

use App\Filament\Clusters\ProductPricing\Resources\PriceBookItems\PriceBookItemResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManagePriceBookItems extends ManageRecords
{
    protected static string $resource = PriceBookItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}

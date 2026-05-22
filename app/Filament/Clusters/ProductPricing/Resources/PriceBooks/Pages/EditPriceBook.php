<?php

namespace App\Filament\Clusters\ProductPricing\Resources\PriceBooks\Pages;

use App\Filament\Clusters\ProductPricing\Resources\PriceBooks\PriceBookResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditPriceBook extends EditRecord
{
    protected static string $resource = PriceBookResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}

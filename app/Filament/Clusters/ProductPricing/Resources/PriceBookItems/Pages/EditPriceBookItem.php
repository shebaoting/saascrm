<?php

namespace App\Filament\Clusters\ProductPricing\Resources\PriceBookItems\Pages;

use App\Filament\Clusters\ProductPricing\Resources\PriceBookItems\PriceBookItemResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditPriceBookItem extends EditRecord
{
    protected static string $resource = PriceBookItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}

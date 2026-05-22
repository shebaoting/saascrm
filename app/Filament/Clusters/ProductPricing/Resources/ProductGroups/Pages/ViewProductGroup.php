<?php

namespace App\Filament\Clusters\ProductPricing\Resources\ProductGroups\Pages;

use App\Filament\Clusters\ProductPricing\Resources\ProductGroups\ProductGroupResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewProductGroup extends ViewRecord
{
    protected static string $resource = ProductGroupResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}

<?php

namespace App\Filament\Clusters\OrderFinance\Resources\Orders\Pages;

use App\Filament\Clusters\OrderFinance\Resources\Orders\OrderResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewOrder extends ViewRecord
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}

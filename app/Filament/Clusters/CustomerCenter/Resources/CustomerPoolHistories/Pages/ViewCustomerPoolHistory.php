<?php

namespace App\Filament\Clusters\CustomerCenter\Resources\CustomerPoolHistories\Pages;

use App\Filament\Clusters\CustomerCenter\Resources\CustomerPoolHistories\CustomerPoolHistoryResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewCustomerPoolHistory extends ViewRecord
{
    protected static string $resource = CustomerPoolHistoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}

<?php

namespace App\Filament\Clusters\SalesProcess\Resources\SalesTargets\Pages;

use App\Filament\Clusters\SalesProcess\Resources\SalesTargets\SalesTargetResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewSalesTarget extends ViewRecord
{
    protected static string $resource = SalesTargetResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}

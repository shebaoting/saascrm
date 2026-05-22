<?php

namespace App\Filament\Clusters\SalesProcess\Resources\SalesTargets\Pages;

use App\Filament\Clusters\SalesProcess\Resources\SalesTargets\SalesTargetResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSalesTargets extends ListRecords
{
    protected static string $resource = SalesTargetResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}

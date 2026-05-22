<?php

namespace App\Filament\Clusters\SalesProcess\Resources\Opportunities\Pages;

use App\Filament\Clusters\SalesProcess\Resources\Opportunities\OpportunityResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListOpportunities extends ListRecords
{
    protected static string $resource = OpportunityResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}

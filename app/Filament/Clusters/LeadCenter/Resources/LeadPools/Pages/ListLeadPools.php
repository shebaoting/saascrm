<?php

namespace App\Filament\Clusters\LeadCenter\Resources\LeadPools\Pages;

use App\Filament\Clusters\LeadCenter\Resources\LeadPools\LeadPoolResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListLeadPools extends ListRecords
{
    protected static string $resource = LeadPoolResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}

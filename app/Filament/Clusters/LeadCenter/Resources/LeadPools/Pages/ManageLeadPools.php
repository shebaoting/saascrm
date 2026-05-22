<?php

namespace App\Filament\Clusters\LeadCenter\Resources\LeadPools\Pages;

use App\Filament\Clusters\LeadCenter\Resources\LeadPools\LeadPoolResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageLeadPools extends ManageRecords
{
    protected static string $resource = LeadPoolResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}

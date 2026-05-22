<?php

namespace App\Filament\Clusters\LeadCenter\Resources\LeadPools\Pages;

use App\Filament\Clusters\LeadCenter\Resources\LeadPools\LeadPoolResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewLeadPool extends ViewRecord
{
    protected static string $resource = LeadPoolResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}

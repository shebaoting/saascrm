<?php

namespace App\Filament\Clusters\LeadCenter\Resources\Leads\Pages;

use App\Filament\Clusters\LeadCenter\Resources\Leads\LeadResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewLead extends ViewRecord
{
    protected static string $resource = LeadResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}

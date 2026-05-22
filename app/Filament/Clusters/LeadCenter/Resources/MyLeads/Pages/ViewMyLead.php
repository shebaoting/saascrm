<?php

namespace App\Filament\Clusters\LeadCenter\Resources\MyLeads\Pages;

use App\Filament\Clusters\LeadCenter\Resources\MyLeads\MyLeadResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewMyLead extends ViewRecord
{
    protected static string $resource = MyLeadResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}

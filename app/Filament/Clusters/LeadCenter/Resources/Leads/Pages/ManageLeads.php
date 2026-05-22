<?php

namespace App\Filament\Clusters\LeadCenter\Resources\Leads\Pages;

use App\Filament\Clusters\LeadCenter\Resources\Leads\LeadResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageLeads extends ManageRecords
{
    protected static string $resource = LeadResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}

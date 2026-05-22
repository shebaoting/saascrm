<?php

namespace App\Filament\Clusters\LeadCenter\Resources\MyLeads\Pages;

use App\Filament\Clusters\LeadCenter\Resources\MyLeads\MyLeadResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageMyLeads extends ManageRecords
{
    protected static string $resource = MyLeadResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}

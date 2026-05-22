<?php

namespace App\Filament\Clusters\LeadCenter\Resources\MyLeads\Pages;

use App\Filament\Clusters\LeadCenter\Resources\MyLeads\MyLeadResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListMyLeads extends ListRecords
{
    protected static string $resource = MyLeadResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}

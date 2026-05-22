<?php

namespace App\Filament\Clusters\LeadCenter\Resources\MyLeads\Pages;

use App\Filament\Clusters\LeadCenter\Resources\MyLeads\MyLeadResource;
use Filament\Resources\Pages\CreateRecord;

class CreateMyLead extends CreateRecord
{
    protected static string $resource = MyLeadResource::class;
}

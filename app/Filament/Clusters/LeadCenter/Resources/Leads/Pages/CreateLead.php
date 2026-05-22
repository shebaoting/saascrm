<?php

namespace App\Filament\Clusters\LeadCenter\Resources\Leads\Pages;

use App\Filament\Clusters\LeadCenter\Resources\Leads\LeadResource;
use Filament\Resources\Pages\CreateRecord;

class CreateLead extends CreateRecord
{
    protected static string $resource = LeadResource::class;
}

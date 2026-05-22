<?php

namespace App\Filament\Clusters\SalesProcess\Resources\Opportunities\Pages;

use App\Filament\Clusters\SalesProcess\Resources\Opportunities\OpportunityResource;
use Filament\Resources\Pages\CreateRecord;

class CreateOpportunity extends CreateRecord
{
    protected static string $resource = OpportunityResource::class;
}

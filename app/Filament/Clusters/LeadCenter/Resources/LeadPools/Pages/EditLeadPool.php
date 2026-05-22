<?php

namespace App\Filament\Clusters\LeadCenter\Resources\LeadPools\Pages;

use App\Filament\Clusters\LeadCenter\Resources\LeadPools\LeadPoolResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditLeadPool extends EditRecord
{
    protected static string $resource = LeadPoolResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}

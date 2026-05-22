<?php

namespace App\Filament\Clusters\LeadCenter\Resources\MyLeads\Pages;

use App\Filament\Clusters\LeadCenter\Resources\MyLeads\MyLeadResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditMyLead extends EditRecord
{
    protected static string $resource = MyLeadResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}

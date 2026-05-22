<?php

namespace App\Filament\Clusters\SalesProcess\Resources\SalesTargets\Pages;

use App\Filament\Clusters\SalesProcess\Resources\SalesTargets\SalesTargetResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditSalesTarget extends EditRecord
{
    protected static string $resource = SalesTargetResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}

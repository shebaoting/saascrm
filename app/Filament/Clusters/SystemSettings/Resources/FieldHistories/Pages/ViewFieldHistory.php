<?php

namespace App\Filament\Clusters\SystemSettings\Resources\FieldHistories\Pages;

use App\Filament\Clusters\SystemSettings\Resources\FieldHistories\FieldHistoryResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewFieldHistory extends ViewRecord
{
    protected static string $resource = FieldHistoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}

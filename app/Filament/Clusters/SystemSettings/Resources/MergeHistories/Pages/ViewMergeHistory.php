<?php

namespace App\Filament\Clusters\SystemSettings\Resources\MergeHistories\Pages;

use App\Filament\Clusters\SystemSettings\Resources\MergeHistories\MergeHistoryResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewMergeHistory extends ViewRecord
{
    protected static string $resource = MergeHistoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}

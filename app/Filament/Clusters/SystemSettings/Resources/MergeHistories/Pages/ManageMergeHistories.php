<?php

namespace App\Filament\Clusters\SystemSettings\Resources\MergeHistories\Pages;

use App\Filament\Clusters\SystemSettings\Resources\MergeHistories\MergeHistoryResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageMergeHistories extends ManageRecords
{
    protected static string $resource = MergeHistoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}

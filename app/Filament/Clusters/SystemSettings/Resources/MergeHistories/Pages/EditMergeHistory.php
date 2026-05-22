<?php

namespace App\Filament\Clusters\SystemSettings\Resources\MergeHistories\Pages;

use App\Filament\Clusters\SystemSettings\Resources\MergeHistories\MergeHistoryResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditMergeHistory extends EditRecord
{
    protected static string $resource = MergeHistoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}

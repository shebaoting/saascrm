<?php

namespace App\Filament\Clusters\SystemSettings\Resources\FieldHistories\Pages;

use App\Filament\Clusters\SystemSettings\Resources\FieldHistories\FieldHistoryResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditFieldHistory extends EditRecord
{
    protected static string $resource = FieldHistoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}

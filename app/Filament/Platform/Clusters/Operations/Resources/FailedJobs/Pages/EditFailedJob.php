<?php

namespace App\Filament\Platform\Clusters\Operations\Resources\FailedJobs\Pages;

use App\Filament\Platform\Clusters\Operations\Resources\FailedJobs\FailedJobResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditFailedJob extends EditRecord
{
    protected static string $resource = FailedJobResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}

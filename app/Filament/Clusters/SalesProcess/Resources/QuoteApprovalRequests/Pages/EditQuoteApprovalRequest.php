<?php

namespace App\Filament\Clusters\SalesProcess\Resources\QuoteApprovalRequests\Pages;

use App\Filament\Clusters\SalesProcess\Resources\QuoteApprovalRequests\QuoteApprovalRequestResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditQuoteApprovalRequest extends EditRecord
{
    protected static string $resource = QuoteApprovalRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}

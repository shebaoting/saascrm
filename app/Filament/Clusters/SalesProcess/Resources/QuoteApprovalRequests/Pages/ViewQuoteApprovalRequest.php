<?php

namespace App\Filament\Clusters\SalesProcess\Resources\QuoteApprovalRequests\Pages;

use App\Filament\Clusters\SalesProcess\Resources\QuoteApprovalRequests\QuoteApprovalRequestResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewQuoteApprovalRequest extends ViewRecord
{
    protected static string $resource = QuoteApprovalRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}

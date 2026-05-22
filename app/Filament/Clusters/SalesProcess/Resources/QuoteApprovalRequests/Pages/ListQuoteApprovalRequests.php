<?php

namespace App\Filament\Clusters\SalesProcess\Resources\QuoteApprovalRequests\Pages;

use App\Filament\Clusters\SalesProcess\Resources\QuoteApprovalRequests\QuoteApprovalRequestResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListQuoteApprovalRequests extends ListRecords
{
    protected static string $resource = QuoteApprovalRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}

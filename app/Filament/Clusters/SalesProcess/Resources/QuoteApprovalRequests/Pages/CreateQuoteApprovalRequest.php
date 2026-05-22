<?php

namespace App\Filament\Clusters\SalesProcess\Resources\QuoteApprovalRequests\Pages;

use App\Filament\Clusters\SalesProcess\Resources\QuoteApprovalRequests\QuoteApprovalRequestResource;
use Filament\Resources\Pages\CreateRecord;

class CreateQuoteApprovalRequest extends CreateRecord
{
    protected static string $resource = QuoteApprovalRequestResource::class;
}

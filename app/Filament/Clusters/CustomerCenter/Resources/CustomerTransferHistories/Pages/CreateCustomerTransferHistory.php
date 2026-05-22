<?php

namespace App\Filament\Clusters\CustomerCenter\Resources\CustomerTransferHistories\Pages;

use App\Filament\Clusters\CustomerCenter\Resources\CustomerTransferHistories\CustomerTransferHistoryResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCustomerTransferHistory extends CreateRecord
{
    protected static string $resource = CustomerTransferHistoryResource::class;
}

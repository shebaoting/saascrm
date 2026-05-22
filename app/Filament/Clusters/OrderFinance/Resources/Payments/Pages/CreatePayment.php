<?php

namespace App\Filament\Clusters\OrderFinance\Resources\Payments\Pages;

use App\Filament\Clusters\OrderFinance\Resources\Payments\PaymentResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePayment extends CreateRecord
{
    protected static string $resource = PaymentResource::class;
}

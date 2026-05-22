<?php

namespace App\Filament\Clusters\OrderFinance\Resources\OrderItems\Pages;

use App\Filament\Clusters\OrderFinance\Resources\OrderItems\OrderItemResource;
use Filament\Resources\Pages\CreateRecord;

class CreateOrderItem extends CreateRecord
{
    protected static string $resource = OrderItemResource::class;
}

<?php

namespace App\Filament\Clusters\SalesProcess\Resources\Quotes\Pages;

use App\Filament\Clusters\SalesProcess\Resources\Quotes\QuoteResource;
use Filament\Resources\Pages\CreateRecord;

class CreateQuote extends CreateRecord
{
    protected static string $resource = QuoteResource::class;
}

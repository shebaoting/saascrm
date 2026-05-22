<?php

namespace App\Filament\Clusters\SalesProcess\Resources\Quotes\Pages;

use App\Filament\Clusters\SalesProcess\Resources\Quotes\QuoteResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewQuote extends ViewRecord
{
    protected static string $resource = QuoteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}

<?php

namespace App\Filament\Clusters\SalesProcess\Resources\Quotes\Pages;

use App\Filament\Clusters\SalesProcess\Resources\Quotes\QuoteResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageQuotes extends ManageRecords
{
    protected static string $resource = QuoteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}

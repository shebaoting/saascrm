<?php

namespace App\Filament\Clusters\CustomerCenter\Resources\Contacts\Pages;

use App\Filament\Clusters\CustomerCenter\Resources\Contacts\ContactResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListContacts extends ListRecords
{
    protected static string $resource = ContactResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}

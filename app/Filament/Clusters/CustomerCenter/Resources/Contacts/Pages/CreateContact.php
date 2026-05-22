<?php

namespace App\Filament\Clusters\CustomerCenter\Resources\Contacts\Pages;

use App\Filament\Clusters\CustomerCenter\Resources\Contacts\ContactResource;
use Filament\Resources\Pages\CreateRecord;

class CreateContact extends CreateRecord
{
    protected static string $resource = ContactResource::class;
}

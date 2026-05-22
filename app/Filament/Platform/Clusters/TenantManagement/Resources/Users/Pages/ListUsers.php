<?php

namespace App\Filament\Platform\Clusters\TenantManagement\Resources\Users\Pages;

use App\Filament\Platform\Clusters\TenantManagement\Resources\Users\UserResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}

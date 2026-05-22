<?php

namespace App\Filament\Clusters\SystemSettings\Resources\Users\Pages;

use App\Filament\Clusters\SystemSettings\Resources\Users\UserResource;
use App\Models\User;
use Filament\Facades\Filament;
use Filament\Resources\Pages\CreateRecord;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected function afterCreate(): void
    {
        if (! $this->record instanceof User) {
            return;
        }

        $tenant = Filament::getTenant();

        if (! $tenant || $this->record->tenants()->whereKey($tenant->getKey())->exists()) {
            return;
        }

        $this->record->tenants()->attach($tenant->id, [
            'member_name' => $this->record->name,
            'is_owner' => false,
            'is_admin' => false,
            'status' => 'active',
            'joined_at' => now(),
        ]);
    }
}

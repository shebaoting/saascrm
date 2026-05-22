<?php

namespace App\Filament\Clusters\SystemSettings\Resources\Users\Pages;

use App\Filament\Clusters\SystemSettings\Resources\Users\UserResource;
use App\Models\User;
use Filament\Actions\CreateAction;
use Filament\Facades\Filament;
use Filament\Resources\Pages\ManageRecords;

class ManageUsers extends ManageRecords
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->after(function (User $record): void {
                    $tenant = Filament::getTenant();

                    if (! $tenant || $record->tenants()->whereKey($tenant->getKey())->exists()) {
                        return;
                    }

                    $record->tenants()->attach($tenant->id, [
                        'member_name' => $record->name,
                        'is_owner' => false,
                        'is_admin' => false,
                        'status' => 'active',
                        'joined_at' => now(),
                    ]);
                }),
        ];
    }
}

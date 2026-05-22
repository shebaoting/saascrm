<?php

namespace App\Filament\Clusters\SystemSettings\Resources\TenantInvitations\Schemas;

use App\Models\Department;
use App\Models\Role;
use App\Support\CrmAccess;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class TenantInvitationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('email')
                    ->label('邮箱')
                    ->email(),
                TextInput::make('phone')
                    ->tel(),
                Select::make('role_ids')
                    ->multiple()
                    ->options(fn (): array => Role::query()
                        ->where('tenant_id', CrmAccess::tenantId())
                        ->orderBy('name')
                        ->pluck('name', 'id')
                        ->all()),
                Select::make('department_ids')
                    ->multiple()
                    ->options(fn (): array => Department::query()
                        ->where('tenant_id', CrmAccess::tenantId())
                        ->orderBy('name')
                        ->pluck('name', 'id')
                        ->all()),
                DateTimePicker::make('expires_at')
                    ->default(fn () => now()->addDays(7)),
            ]);
    }
}

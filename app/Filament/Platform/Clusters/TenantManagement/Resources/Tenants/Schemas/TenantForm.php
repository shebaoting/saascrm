<?php

namespace App\Filament\Platform\Clusters\TenantManagement\Resources\Tenants\Schemas;

use App\Support\Filament\CrmUi;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class TenantForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                TextInput::make('slug')
                    ->required(),
                TextInput::make('short_name'),
                TextInput::make('logo_path'),
                TextInput::make('country_code')
                    ->required()
                    ->default('CN'),
                TextInput::make('timezone')
                    ->required()
                    ->default('Asia/Shanghai'),
                TextInput::make('currency')
                    ->required()
                    ->default('CNY'),
                TextInput::make('contact_name'),
                TextInput::make('contact_phone')
                    ->tel(),
                TextInput::make('contact_email')
                    ->email(),
                TextInput::make('address'),
                Select::make('status')
                    ->options(CrmUi::options('tenant.status'))
                    ->required()
                    ->default('trial'),
                DateTimePicker::make('trial_ends_at'),
                TextInput::make('settings'),
            ]);
    }
}

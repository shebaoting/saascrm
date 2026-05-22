<?php

namespace App\Filament\Platform\Clusters\TenantManagement\Resources\Users\Schemas;

use App\Support\Filament\CrmUi;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                TextInput::make('email')
                    ->label('邮箱')
                    ->email()
                    ->required(),
                DateTimePicker::make('email_verified_at'),
                TextInput::make('password')
                    ->password()
                    ->required(),
                TextInput::make('phone')
                    ->tel(),
                TextInput::make('avatar'),
                Select::make('gender')
                    ->options(CrmUi::options('gender')),
                TextInput::make('position'),
                TextInput::make('telephone')
                    ->tel(),
                TextInput::make('locale')
                    ->required()
                    ->default('zh_CN'),
                Toggle::make('status')
                    ->required(),
                Toggle::make('is_platform_admin')
                    ->required(),
                DateTimePicker::make('last_login_at'),
            ]);
    }
}

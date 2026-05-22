<?php

namespace App\Filament\Clusters\CustomerCenter\Resources\Contacts\Schemas;

use App\Support\Filament\CrmUi;
use App\Support\Filament\CustomFieldUi;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ContactForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('customer_id')
                    ->relationship('customer', 'name')
                    ->required(),
                TextInput::make('name')
                    ->required(),
                TextInput::make('phone')
                    ->tel(),
                TextInput::make('email')
                    ->label('邮箱')
                    ->email(),
                TextInput::make('wechat_id'),
                Select::make('gender')
                    ->options(CrmUi::options('gender')),
                TextInput::make('position'),
                TextInput::make('department'),
                TextInput::make('avatar'),
                Toggle::make('is_primary')
                    ->required(),
                ...CustomFieldUi::formSections('contact'),
            ]);
    }
}

<?php

namespace App\Filament\Clusters\LeadCenter\Resources\Leads\Schemas;

use App\Support\Filament\CrmUi;
use App\Support\Filament\CustomFieldUi;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class LeadForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('company_name'),
                TextInput::make('contact_name'),
                TextInput::make('phone')
                    ->tel(),
                TextInput::make('email')
                    ->label('邮箱')
                    ->email(),
                TextInput::make('wechat_id'),
                TextInput::make('country_code'),
                TextInput::make('area_id'),
                TextInput::make('address'),
                TextInput::make('source'),
                TagsInput::make('tags'),
                Select::make('status')
                    ->options(CrmUi::options('lead.status'))
                    ->required()
                    ->default('new'),
                Select::make('qualification_status')
                    ->options(CrmUi::options('lead.qualification_status')),
                Select::make('owner_user_id')
                    ->relationship('owner', 'name'),
                TextInput::make('lost_reason'),
                ...CustomFieldUi::formSections('lead'),
            ]);
    }
}

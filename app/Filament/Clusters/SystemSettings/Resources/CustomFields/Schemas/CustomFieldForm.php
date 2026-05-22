<?php

namespace App\Filament\Clusters\SystemSettings\Resources\CustomFields\Schemas;

use App\Support\Filament\CrmUi;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class CustomFieldForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('model_type')
                    ->options(CrmUi::options('target_type'))
                    ->required(),
                TextInput::make('group_name'),
                Select::make('type')
                    ->options(CrmUi::options('custom_field.type'))
                    ->required(),
                TextInput::make('identifier')
                    ->required(),
                TextInput::make('name')
                    ->required(),
                TextInput::make('sort_order')
                    ->numeric(),
                Toggle::make('is_visible')
                    ->required(),
                Toggle::make('is_required')
                    ->required(),
                Toggle::make('is_filterable')
                    ->required(),
                Toggle::make('is_list_visible')
                    ->required(),
                Toggle::make('is_show_in_tracking')
                    ->required(),
                KeyValue::make('data')
                    ->columnSpanFull(),
            ]);
    }
}

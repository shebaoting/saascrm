<?php

namespace App\Filament\Clusters\CustomerCenter\Resources\CustomerTransferHistories\Schemas;

use App\Support\Filament\CrmUi;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class CustomerTransferHistoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('target_type')
                    ->options(CrmUi::options('target_type'))
                    ->required(),
                TextInput::make('target_id')
                    ->required()
                    ->numeric(),
                Select::make('from_user_id')
                    ->relationship('fromUser', 'name'),
                Select::make('to_user_id')
                    ->relationship('toUser', 'name'),
                TextInput::make('reason'),
                Select::make('operated_by')
                    ->relationship('operatorUser', 'name')
                    ->required(),
            ]);
    }
}

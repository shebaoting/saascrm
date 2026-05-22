<?php

namespace App\Filament\Clusters\CustomerCenter\Resources\CustomerPoolHistories\Schemas;

use App\Support\Filament\CrmUi;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class CustomerPoolHistoryForm
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
                Select::make('action')
                    ->options([
                        'claim' => '领取',
                        'release' => '释放',
                        'transfer' => '转移',
                        'auto_recycle' => '自动回收',
                    ])
                    ->required(),
                Select::make('from_user_id')
                    ->relationship('fromUser', 'name'),
                Select::make('to_user_id')
                    ->relationship('toUser', 'name'),
                TextInput::make('reason'),
                Select::make('operated_by')
                    ->relationship('operatorUser', 'name'),
            ]);
    }
}

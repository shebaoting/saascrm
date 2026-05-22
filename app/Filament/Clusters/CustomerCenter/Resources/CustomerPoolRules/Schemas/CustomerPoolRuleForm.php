<?php

namespace App\Filament\Clusters\CustomerCenter\Resources\CustomerPoolRules\Schemas;

use App\Models\Department;
use App\Support\Filament\CrmUi;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class CustomerPoolRuleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('target_type')
                    ->options(CrmUi::options('target_type'))
                    ->required(),
                TextInput::make('name')
                    ->required(),
                TextInput::make('inactive_days')
                    ->required()
                    ->numeric()
                    ->default(30),
                TextInput::make('protect_days')
                    ->required()
                    ->numeric()
                    ->default(7),
                TextInput::make('max_claim_daily')
                    ->numeric(),
                Select::make('department_ids')
                    ->multiple()
                    ->options(fn (): array => Department::query()->orderBy('name')->pluck('name', 'id')->all()),
                Toggle::make('is_active')
                    ->required(),
            ]);
    }
}

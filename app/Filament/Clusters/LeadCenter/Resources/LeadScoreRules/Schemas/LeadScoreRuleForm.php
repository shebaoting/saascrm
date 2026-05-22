<?php

namespace App\Filament\Clusters\LeadCenter\Resources\LeadScoreRules\Schemas;

use App\Filament\Clusters\LeadCenter\Resources\LeadScoreRules\LeadScoreRuleResource;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class LeadScoreRuleForm
{
    public static function configure(Schema $schema, string $resourceClass = LeadScoreRuleResource::class): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                Select::make('field')
                    ->options($resourceClass::fieldOptions())
                    ->searchable()
                    ->required(),
                Select::make('operator')
                    ->options($resourceClass::operatorOptions())
                    ->required(),
                TagsInput::make('value')
                    ->separator(',')
                    ->placeholder('可填多个值'),
                TextInput::make('score')
                    ->required()
                    ->numeric()
                    ->default(0),
                Toggle::make('is_active')
                    ->default(true)
                    ->required(),
            ]);
    }
}

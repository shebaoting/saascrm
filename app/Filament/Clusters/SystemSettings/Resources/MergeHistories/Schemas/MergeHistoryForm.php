<?php

namespace App\Filament\Clusters\SystemSettings\Resources\MergeHistories\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class MergeHistoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('model_type')
                    ->required(),
                TextInput::make('source_id')
                    ->required()
                    ->numeric(),
                TextInput::make('target_id')
                    ->required()
                    ->numeric(),
                TextInput::make('merged_fields'),
                TextInput::make('merged_relations'),
                Select::make('merged_by')
                    ->relationship('mergedBy', 'name')
                    ->required(),
            ]);
    }
}

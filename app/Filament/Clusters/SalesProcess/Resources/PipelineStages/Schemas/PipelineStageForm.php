<?php

namespace App\Filament\Clusters\SalesProcess\Resources\PipelineStages\Schemas;

use App\Support\Filament\CrmUi;
use App\Support\Filament\CustomFieldUi;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class PipelineStageForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('pipeline_id')
                    ->relationship('pipeline', 'name')
                    ->required(),
                TextInput::make('name')
                    ->required(),
                TextInput::make('probability')
                    ->required()
                    ->numeric()
                    ->default(0),
                Select::make('stage_type')
                    ->options(CrmUi::options('pipeline.stage_type'))
                    ->required()
                    ->default('open'),
                CheckboxList::make('required_fields')
                    ->options(fn (): array => CustomFieldUi::requiredFieldOptions('opportunity'))
                    ->columns(2)
                    ->bulkToggleable()
                    ->columnSpanFull(),
                TextInput::make('sort_order')
                    ->required()
                    ->numeric()
                    ->default(0),
                Toggle::make('is_active')
                    ->required(),
            ]);
    }
}

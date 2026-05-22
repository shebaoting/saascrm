<?php

namespace App\Filament\Clusters\SystemSettings\Resources\FailedImportRows\Schemas;

use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class FailedImportRowForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('import_id')
                    ->relationship('import', 'file_name')
                    ->required(),
                KeyValue::make('data')
                    ->required()
                    ->columnSpanFull(),
                Textarea::make('validation_error')
                    ->columnSpanFull(),
            ]);
    }
}

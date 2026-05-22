<?php

namespace App\Filament\Clusters\SystemSettings\Resources\FailedImportRows\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class FailedImportRowInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('import.file_name')
                    ->label('导入任务'),
                TextEntry::make('validation_error')
                    ->placeholder('-')
                    ->columnSpanFull(),
            ]);
    }
}

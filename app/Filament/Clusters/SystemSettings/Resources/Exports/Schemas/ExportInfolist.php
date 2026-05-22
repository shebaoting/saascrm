<?php

namespace App\Filament\Clusters\SystemSettings\Resources\Exports\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class ExportInfolist
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
                TextEntry::make('completed_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('file_disk'),
                TextEntry::make('file_name')
                    ->placeholder('-'),
                TextEntry::make('exporter'),
                TextEntry::make('processed_rows')
                    ->numeric(),
                TextEntry::make('total_rows')
                    ->numeric(),
                TextEntry::make('successful_rows')
                    ->numeric(),
                TextEntry::make('user.name'),
            ]);
    }
}

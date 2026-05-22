<?php

namespace App\Filament\Clusters\SystemSettings\Resources\DuplicateRecords\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class DuplicateRecordInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('target_type'),
                TextEntry::make('target_id')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('matched_type'),
                TextEntry::make('matched_id')
                    ->numeric(),
                TextEntry::make('field_name'),
                TextEntry::make('field_value'),
                TextEntry::make('status'),
                TextEntry::make('payload')
                    ->formatStateUsing(fn (mixed $state): string => json_encode($state, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT))
                    ->columnSpanFull(),
            ]);
    }
}

<?php

namespace App\Filament\Clusters\SystemSettings\Resources\MergeHistories\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class MergeHistoryInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('model_type'),
                TextEntry::make('source_id')
                    ->numeric(),
                TextEntry::make('target_id')
                    ->numeric(),
                TextEntry::make('mergedBy.name'),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}

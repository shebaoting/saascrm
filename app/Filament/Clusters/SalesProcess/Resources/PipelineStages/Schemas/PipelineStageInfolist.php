<?php

namespace App\Filament\Clusters\SalesProcess\Resources\PipelineStages\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class PipelineStageInfolist
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
                TextEntry::make('pipeline.name')
                    ->label('销售管道'),
                TextEntry::make('name'),
                TextEntry::make('probability')
                    ->numeric(),
                TextEntry::make('stage_type'),
                TextEntry::make('sort_order')
                    ->numeric(),
                IconEntry::make('is_active')
                    ->boolean(),
            ]);
    }
}

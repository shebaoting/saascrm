<?php

namespace App\Filament\Clusters\LeadCenter\Resources\LeadScoreRules\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class LeadScoreRuleInfolist
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
                TextEntry::make('name'),
                TextEntry::make('field'),
                TextEntry::make('operator'),
                TextEntry::make('value')
                    ->formatStateUsing(fn ($state): string => implode('、', \Illuminate\Support\Arr::wrap($state))),
                TextEntry::make('score')
                    ->numeric(),
                IconEntry::make('is_active')
                    ->boolean(),
            ]);
    }
}

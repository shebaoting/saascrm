<?php

namespace App\Filament\Clusters\CustomerCenter\Resources\CustomerPoolRules\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class CustomerPoolRuleInfolist
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
                TextEntry::make('target_type'),
                TextEntry::make('name'),
                TextEntry::make('inactive_days')
                    ->numeric(),
                TextEntry::make('protect_days')
                    ->numeric(),
                TextEntry::make('max_claim_daily')
                    ->numeric()
                    ->placeholder('-'),
                IconEntry::make('is_active')
                    ->boolean(),
            ]);
    }
}

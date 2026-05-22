<?php

namespace App\Filament\Clusters\SystemSettings\Resources\AutomationRules\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class AutomationRuleInfolist
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
                TextEntry::make('trigger_type'),
                TextEntry::make('target_type'),
                IconEntry::make('is_active')
                    ->boolean(),
                TextEntry::make('last_run_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}

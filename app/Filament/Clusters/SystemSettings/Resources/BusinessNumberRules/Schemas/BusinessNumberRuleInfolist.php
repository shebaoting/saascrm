<?php

namespace App\Filament\Clusters\SystemSettings\Resources\BusinessNumberRules\Schemas;

use App\Services\Crm\BusinessNumberService;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class BusinessNumberRuleInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextEntry::make('module')
                ->formatStateUsing(fn (string $state): string => BusinessNumberService::moduleLabels()[$state] ?? $state),
            TextEntry::make('pattern'),
            TextEntry::make('current_sequence')
                ->numeric(),
            TextEntry::make('last_sequence_key')
                ->placeholder('-'),
            IconEntry::make('is_active')
                ->boolean(),
        ]);
    }
}

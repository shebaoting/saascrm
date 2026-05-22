<?php

namespace App\Filament\Clusters\LeadCenter\Resources\AssignmentRules\Schemas;

use App\Models\AssignmentRule;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class AssignmentRuleInfolist
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
                TextEntry::make('target_type'),
                TextEntry::make('method'),
                TextEntry::make('department.name')
                    ->placeholder('-'),
                TextEntry::make('max_per_user_daily')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('conditions_count')
                    ->state(fn (AssignmentRule $record): int => $record->conditions()->count())
                    ->label('条件数'),
                TextEntry::make('priority')
                    ->numeric(),
                IconEntry::make('is_active')
                    ->boolean(),
            ]);
    }
}

<?php

namespace App\Filament\Clusters\SystemSettings\Resources\AutomationActions\Schemas;

use App\Support\Filament\CrmUi;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class AutomationActionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('automation_rule_id')
                    ->relationship('rule', 'name')
                    ->required(),
                Select::make('action_type')
                    ->options(CrmUi::options('automation.action_type'))
                    ->required(),
                Textarea::make('payload')
                    ->formatStateUsing(fn ($state): ?string => is_array($state) ? json_encode($state, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) : $state)
                    ->dehydrateStateUsing(function (?string $state): ?array {
                        $decoded = blank($state) ? null : json_decode($state, true);

                        return is_array($decoded) ? $decoded : null;
                    }),
                TextInput::make('sort_order')
                    ->required()
                    ->numeric()
                    ->default(0),
            ]);
    }
}

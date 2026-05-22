<?php

namespace App\Filament\Platform\Clusters\SubscriptionBilling\Resources\Plans\Schemas;

use App\Services\Crm\PlanLimitService;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class PlanInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('name'),
                TextEntry::make('code'),
                TextEntry::make('price_monthly')
                    ->numeric(),
                TextEntry::make('price_yearly')
                    ->numeric(),
                TextEntry::make('max_users')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('max_leads')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('max_customers')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('max_storage_mb')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('max_custom_fields')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('max_automation_rules')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('max_imports_daily')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('max_exports_daily')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('features')
                    ->formatStateUsing(fn (?array $state): string => collect($state ?: [])
                        ->filter()
                        ->keys()
                        ->map(fn (string $key): string => PlanLimitService::featureLabels()[$key] ?? $key)
                        ->join('、'))
                    ->placeholder('-'),
                IconEntry::make('is_active')
                    ->boolean(),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}

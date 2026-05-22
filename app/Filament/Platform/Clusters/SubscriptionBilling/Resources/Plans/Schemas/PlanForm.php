<?php

namespace App\Filament\Platform\Clusters\SubscriptionBilling\Resources\Plans\Schemas;

use App\Services\Crm\PlanLimitService;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class PlanForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                TextInput::make('code')
                    ->required(),
                TextInput::make('price_monthly')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('price_yearly')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('max_users')
                    ->numeric(),
                TextInput::make('max_leads')
                    ->numeric(),
                TextInput::make('max_customers')
                    ->numeric(),
                TextInput::make('max_storage_mb')
                    ->numeric(),
                TextInput::make('max_custom_fields')
                    ->numeric(),
                TextInput::make('max_automation_rules')
                    ->numeric(),
                TextInput::make('max_imports_daily')
                    ->numeric(),
                TextInput::make('max_exports_daily')
                    ->numeric(),
                CheckboxList::make('features')
                    ->label('功能开关')
                    ->options(PlanLimitService::featureLabels())
                    ->columns(3)
                    ->afterStateHydrated(function ($component, ?array $state): void {
                        $component->state(collect($state ?: [])
                            ->filter(fn (mixed $enabled): bool => (bool) $enabled)
                            ->keys()
                            ->all());
                    })
                    ->dehydrateStateUsing(fn (?array $state): array => collect(PlanLimitService::featureLabels())
                        ->mapWithKeys(fn (string $label, string $key): array => [$key => in_array($key, $state ?: [], true)])
                        ->all()),
                Toggle::make('is_active')
                    ->required(),
            ]);
    }
}

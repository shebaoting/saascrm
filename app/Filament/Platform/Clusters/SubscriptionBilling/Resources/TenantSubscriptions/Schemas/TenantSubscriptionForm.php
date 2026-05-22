<?php

namespace App\Filament\Platform\Clusters\SubscriptionBilling\Resources\TenantSubscriptions\Schemas;

use App\Support\Filament\CrmUi;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class TenantSubscriptionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('tenant_id')
                    ->relationship('tenant', 'name')
                    ->required(),
                Select::make('plan_id')
                    ->relationship('plan', 'name')
                    ->required(),
                Select::make('status')
                    ->options(CrmUi::options('subscription.status'))
                    ->required()
                    ->default('trialing'),
                Select::make('billing_cycle')
                    ->options(CrmUi::options('subscription.billing_cycle'))
                    ->required()
                    ->default('manual'),
                DateTimePicker::make('starts_at')
                    ->required(),
                DateTimePicker::make('ends_at'),
                DateTimePicker::make('cancelled_at'),
                TextInput::make('metadata'),
            ]);
    }
}

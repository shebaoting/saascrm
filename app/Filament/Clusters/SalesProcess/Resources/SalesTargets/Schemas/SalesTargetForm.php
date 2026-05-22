<?php

namespace App\Filament\Clusters\SalesProcess\Resources\SalesTargets\Schemas;

use App\Filament\Clusters\SalesProcess\Resources\SalesTargets\SalesTargetResource;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class SalesTargetForm
{
    public static function configure(Schema $schema, string $resourceClass = SalesTargetResource::class): Schema
    {
        return $schema
            ->components([
                Select::make('target_type')
                    ->options($resourceClass::targetTypeOptions())
                    ->required()
                    ->default('user')
                    ->live()
                    ->afterStateUpdated(fn ($state, callable $set) => $set('target_id', null)),
                Select::make('target_id')
                    ->label('目标对象')
                    ->options(fn (Get $get): array => $resourceClass::targetOptions($get('target_type')))
                    ->visible(fn (Get $get): bool => $get('target_type') !== 'tenant')
                    ->required(fn (Get $get): bool => $get('target_type') !== 'tenant'),
                Select::make('period_type')
                    ->options([
                        'week' => '周',
                        'month' => '月',
                        'quarter' => '季度',
                        'year' => '年',
                    ])
                    ->required()
                    ->default('month'),
                DatePicker::make('period_start')
                    ->required(),
                DatePicker::make('period_end')
                    ->required(),
                TextInput::make('target_amount')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('target_payment_amount')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('target_customer_count')
                    ->required()
                    ->numeric()
                    ->default(0),
            ]);
    }
}

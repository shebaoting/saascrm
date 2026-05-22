<?php

namespace App\Filament\Clusters\SalesProcess\Resources\SalesTargets\Schemas;

use App\Filament\Clusters\SalesProcess\Resources\SalesTargets\SalesTargetResource;
use App\Models\SalesTarget;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class SalesTargetInfolist
{
    public static function configure(Schema $schema, string $resourceClass = SalesTargetResource::class): Schema
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
                TextEntry::make('target_id')
                    ->label('目标对象')
                    ->formatStateUsing(fn ($state, SalesTarget $record): string => $resourceClass::targetDisplay($record))
                    ->placeholder('-'),
                TextEntry::make('period_type'),
                TextEntry::make('period_start')
                    ->date(),
                TextEntry::make('period_end')
                    ->date(),
                TextEntry::make('target_amount')
                    ->numeric(),
                TextEntry::make('target_payment_amount')
                    ->numeric(),
                TextEntry::make('target_customer_count')
                    ->numeric(),
            ]);
    }
}

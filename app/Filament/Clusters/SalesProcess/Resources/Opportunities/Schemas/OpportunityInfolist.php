<?php

namespace App\Filament\Clusters\SalesProcess\Resources\Opportunities\Schemas;

use App\Models\Opportunity;
use App\Support\Filament\CustomFieldUi;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class OpportunityInfolist
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
                TextEntry::make('deleted_at')
                    ->dateTime()
                    ->visible(fn (Opportunity $record): bool => $record->trashed()),
                TextEntry::make('customer.name')
                    ->label('客户'),
                TextEntry::make('contact.name')
                    ->label('联系人')
                    ->placeholder('-'),
                TextEntry::make('pipeline.name')
                    ->label('销售管道'),
                TextEntry::make('stage.name'),
                TextEntry::make('name'),
                TextEntry::make('amount')
                    ->numeric(),
                TextEntry::make('closed_amount')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('probability')
                    ->numeric(),
                TextEntry::make('forecast_category'),
                TextEntry::make('expected_close_date')
                    ->date()
                    ->placeholder('-'),
                TextEntry::make('responsible.name')
                    ->placeholder('-'),
                TextEntry::make('closed_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('ended_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('lost_reason')
                    ->placeholder('-'),
                TextEntry::make('lost_remarks')
                    ->placeholder('-'),
                TextEntry::make('invalid_reason')
                    ->placeholder('-'),
                TextEntry::make('invalid_remarks')
                    ->placeholder('-'),
                TextEntry::make('notes')
                    ->placeholder('-'),
                ...CustomFieldUi::infolistSections('opportunity'),
            ]);
    }
}

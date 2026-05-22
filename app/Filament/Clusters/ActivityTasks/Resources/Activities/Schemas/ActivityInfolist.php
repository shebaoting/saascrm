<?php

namespace App\Filament\Clusters\ActivityTasks\Resources\Activities\Schemas;

use App\Models\Activity;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class ActivityInfolist
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
                    ->visible(fn (Activity $record): bool => $record->trashed()),
                TextEntry::make('lead.company_name')
                    ->label('线索')
                    ->placeholder('-'),
                TextEntry::make('customer.name')
                    ->label('客户')
                    ->placeholder('-'),
                TextEntry::make('contact.name')
                    ->label('联系人')
                    ->placeholder('-'),
                TextEntry::make('opportunity.name')
                    ->label('商机')
                    ->placeholder('-'),
                TextEntry::make('type'),
                TextEntry::make('direction')
                    ->placeholder('-'),
                TextEntry::make('subject')
                    ->placeholder('-'),
                TextEntry::make('content')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('outcome')
                    ->placeholder('-'),
                TextEntry::make('occurred_at')
                    ->dateTime(),
                TextEntry::make('next_follow_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('owner.name'),
            ]);
    }
}

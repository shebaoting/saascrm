<?php

namespace App\Filament\Clusters\SalesProcess\Resources\Quotes\Schemas;

use App\Models\Quote;
use App\Support\Filament\CustomFieldUi;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class QuoteInfolist
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
                    ->visible(fn (Quote $record): bool => $record->trashed()),
                TextEntry::make('quote_number'),
                TextEntry::make('version')
                    ->numeric(),
                TextEntry::make('sourceQuote.quote_number')
                    ->placeholder('-'),
                TextEntry::make('title'),
                TextEntry::make('customer.name')
                    ->label('客户'),
                TextEntry::make('contact.name')
                    ->label('联系人')
                    ->placeholder('-'),
                TextEntry::make('opportunity.name')
                    ->label('商机')
                    ->placeholder('-'),
                TextEntry::make('creator.name'),
                TextEntry::make('priceBook.name')
                    ->placeholder('-'),
                TextEntry::make('subtotal_amount')
                    ->numeric(),
                TextEntry::make('discount_amount')
                    ->numeric(),
                TextEntry::make('total_amount')
                    ->numeric(),
                TextEntry::make('total_cost')
                    ->money(),
                TextEntry::make('total_profit')
                    ->numeric(),
                TextEntry::make('total_tax')
                    ->numeric(),
                TextEntry::make('profit_margin')
                    ->numeric(),
                TextEntry::make('status'),
                TextEntry::make('valid_until')
                    ->date()
                    ->placeholder('-'),
                TextEntry::make('notes')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('pdf_path')
                    ->placeholder('-'),
                TextEntry::make('accepted_at')
                    ->dateTime()
                    ->placeholder('-'),
                ...CustomFieldUi::infolistSections('quote'),
            ]);
    }
}

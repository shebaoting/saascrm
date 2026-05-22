<?php

namespace App\Filament\Clusters\CustomerCenter\Resources\Contacts\Schemas;

use App\Models\Contact;
use App\Support\Filament\CustomFieldUi;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class ContactInfolist
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
                    ->visible(fn (Contact $record): bool => $record->trashed()),
                TextEntry::make('customer.name')
                    ->label('客户'),
                TextEntry::make('name'),
                TextEntry::make('phone')
                    ->placeholder('-'),
                TextEntry::make('email')
                    ->label('邮箱')
                    ->placeholder('-'),
                TextEntry::make('wechat_id')
                    ->placeholder('-'),
                TextEntry::make('gender')
                    ->placeholder('-'),
                TextEntry::make('position')
                    ->placeholder('-'),
                TextEntry::make('department')
                    ->placeholder('-'),
                TextEntry::make('avatar')
                    ->placeholder('-'),
                IconEntry::make('is_primary')
                    ->boolean(),
                ...CustomFieldUi::infolistSections('contact'),
            ]);
    }
}

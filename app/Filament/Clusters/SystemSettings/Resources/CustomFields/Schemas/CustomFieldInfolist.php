<?php

namespace App\Filament\Clusters\SystemSettings\Resources\CustomFields\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class CustomFieldInfolist
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
                TextEntry::make('model_type'),
                TextEntry::make('group_name')
                    ->placeholder('-'),
                TextEntry::make('type'),
                TextEntry::make('identifier'),
                TextEntry::make('name'),
                TextEntry::make('sort_order')
                    ->numeric()
                    ->placeholder('-'),
                IconEntry::make('is_visible')
                    ->boolean(),
                IconEntry::make('is_required')
                    ->boolean(),
                IconEntry::make('is_filterable')
                    ->boolean(),
                IconEntry::make('is_list_visible')
                    ->boolean(),
                IconEntry::make('is_show_in_tracking')
                    ->boolean(),
            ]);
    }
}

<?php

namespace App\Filament\Clusters\LeadCenter\Resources\Leads\Schemas;

use App\Models\Lead;
use App\Support\Filament\CustomFieldUi;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class LeadInfolist
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
                    ->visible(fn (Lead $record): bool => $record->trashed()),
                TextEntry::make('lead_number')
                    ->label('线索编号')
                    ->placeholder('-'),
                TextEntry::make('company_name')
                    ->placeholder('-'),
                TextEntry::make('contact_name')
                    ->placeholder('-'),
                TextEntry::make('phone')
                    ->placeholder('-'),
                TextEntry::make('email')
                    ->label('邮箱')
                    ->placeholder('-'),
                TextEntry::make('wechat_id')
                    ->placeholder('-'),
                TextEntry::make('country_code')
                    ->placeholder('-'),
                TextEntry::make('area_id')
                    ->placeholder('-'),
                TextEntry::make('address')
                    ->placeholder('-'),
                TextEntry::make('source')
                    ->placeholder('-'),
                TextEntry::make('status'),
                TextEntry::make('qualification_status')
                    ->placeholder('-'),
                TextEntry::make('score')
                    ->numeric(),
                TextEntry::make('owner.name')
                    ->placeholder('-'),
                TextEntry::make('pool_entered_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('last_activity_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('next_activity_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('convertedCustomer.name')
                    ->label('已转客户')
                    ->placeholder('-'),
                TextEntry::make('converted_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('convertedBy.name')
                    ->placeholder('-'),
                TextEntry::make('lost_reason')
                    ->placeholder('-'),
                ...CustomFieldUi::infolistSections('lead'),
            ]);
    }
}

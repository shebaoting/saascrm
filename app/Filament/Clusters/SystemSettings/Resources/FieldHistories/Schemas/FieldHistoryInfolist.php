<?php

namespace App\Filament\Clusters\SystemSettings\Resources\FieldHistories\Schemas;

use Filament\Infolists\Components\KeyValueEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class FieldHistoryInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextEntry::make('created_at')
                ->label('时间')
                ->dateTime(),
            TextEntry::make('model_type')
                ->label('业务对象')
                ->formatStateUsing(fn (?string $state): string => class_basename((string) $state)),
            TextEntry::make('model_id')
                ->label('记录ID'),
            TextEntry::make('field')
                ->label('字段'),
            KeyValueEntry::make('old_value')
                ->label('修改前'),
            KeyValueEntry::make('new_value')
                ->label('修改后'),
            TextEntry::make('ip_address')
                ->label('IP')
                ->placeholder('-'),
        ]);
    }
}

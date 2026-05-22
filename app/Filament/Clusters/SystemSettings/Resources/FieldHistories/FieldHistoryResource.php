<?php

namespace App\Filament\Clusters\SystemSettings\Resources\FieldHistories;

use App\Filament\Clusters\SystemSettings\Resources\FieldHistories\Pages\ListFieldHistories;
use App\Filament\Clusters\SystemSettings\Resources\FieldHistories\Pages\ViewFieldHistory;
use App\Filament\Clusters\SystemSettings\Resources\FieldHistories\Schemas\FieldHistoryInfolist;
use App\Filament\Clusters\SystemSettings\Resources\FieldHistories\Tables\FieldHistoryTable;
use App\Filament\Clusters\SystemSettings\SystemSettingsCluster;
use App\Filament\Concerns\UsesCrmAccess;
use App\Models\FieldHistory;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class FieldHistoryResource extends Resource
{
    use UsesCrmAccess;

    protected static ?string $model = FieldHistory::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClock;

    protected static ?string $navigationLabel = '字段历史';

    protected static ?string $modelLabel = '字段历史';

    protected static ?string $pluralModelLabel = '字段历史';

    protected static ?string $title = '字段历史';

    protected static bool $hasTitleCaseModelLabel = false;

    protected static ?string $cluster = SystemSettingsCluster::class;

    public static function infolist(Schema $schema): Schema
    {
        return FieldHistoryInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return FieldHistoryTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListFieldHistories::route('/'),
            'view' => ViewFieldHistory::route('/{record}'),
        ];
    }
}

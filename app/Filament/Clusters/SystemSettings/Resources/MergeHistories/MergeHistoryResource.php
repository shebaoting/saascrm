<?php

namespace App\Filament\Clusters\SystemSettings\Resources\MergeHistories;

use App\Filament\Clusters\SystemSettings\Resources\MergeHistories\Pages\CreateMergeHistory;
use App\Filament\Clusters\SystemSettings\Resources\MergeHistories\Pages\EditMergeHistory;
use App\Filament\Clusters\SystemSettings\Resources\MergeHistories\Pages\ListMergeHistories;
use App\Filament\Clusters\SystemSettings\Resources\MergeHistories\Pages\ViewMergeHistory;
use App\Filament\Clusters\SystemSettings\Resources\MergeHistories\Schemas\MergeHistoryForm;
use App\Filament\Clusters\SystemSettings\Resources\MergeHistories\Schemas\MergeHistoryInfolist;
use App\Filament\Clusters\SystemSettings\Resources\MergeHistories\Tables\MergeHistoryTable;
use App\Filament\Clusters\SystemSettings\SystemSettingsCluster;
use App\Filament\Concerns\UsesCrmAccess;
use App\Models\MergeHistory;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class MergeHistoryResource extends Resource
{
    use UsesCrmAccess;

    protected static ?string $model = MergeHistory::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $navigationLabel = '合并历史';

    protected static ?string $modelLabel = '合并历史';

    protected static ?string $pluralModelLabel = '合并历史';

    protected static ?string $title = '合并历史';

    protected static bool $hasTitleCaseModelLabel = false;

    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $cluster = SystemSettingsCluster::class;

    protected static ?string $recordTitleAttribute = 'model_type';

    public static function canCreate(): bool
    {
        return false;
    }

    public static function form(Schema $schema): Schema
    {
        return MergeHistoryForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return MergeHistoryInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MergeHistoryTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMergeHistories::route('/'),
            'create' => CreateMergeHistory::route('/create'),
            'view' => ViewMergeHistory::route('/{record}'),
            'edit' => EditMergeHistory::route('/{record}/edit'),
        ];
    }
}

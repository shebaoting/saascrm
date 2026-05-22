<?php

namespace App\Filament\Clusters\SystemSettings\Resources\Exports;

use App\Filament\Clusters\SystemSettings\Resources\Exports\Pages\CreateExport;
use App\Filament\Clusters\SystemSettings\Resources\Exports\Pages\EditExport;
use App\Filament\Clusters\SystemSettings\Resources\Exports\Pages\ListExports;
use App\Filament\Clusters\SystemSettings\Resources\Exports\Pages\ViewExport;
use App\Filament\Clusters\SystemSettings\Resources\Exports\Schemas\ExportForm;
use App\Filament\Clusters\SystemSettings\Resources\Exports\Schemas\ExportInfolist;
use App\Filament\Clusters\SystemSettings\Resources\Exports\Tables\ExportTable;
use App\Filament\Clusters\SystemSettings\SystemSettingsCluster;
use App\Filament\Concerns\UsesCrmAccess;
use App\Models\Export;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ExportResource extends Resource
{
    use UsesCrmAccess;

    protected static ?string $model = Export::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $navigationLabel = '导出任务';

    protected static ?string $modelLabel = '导出任务';

    protected static ?string $pluralModelLabel = '导出任务';

    protected static ?string $title = '导出任务';

    protected static bool $hasTitleCaseModelLabel = false;

    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $cluster = SystemSettingsCluster::class;

    protected static ?string $recordTitleAttribute = 'exporter';

    public static function canCreate(): bool
    {
        return false;
    }

    public static function form(Schema $schema): Schema
    {
        return ExportForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ExportInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ExportTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListExports::route('/'),
            'create' => CreateExport::route('/create'),
            'view' => ViewExport::route('/{record}'),
            'edit' => EditExport::route('/{record}/edit'),
        ];
    }
}

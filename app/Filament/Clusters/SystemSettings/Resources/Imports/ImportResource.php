<?php

namespace App\Filament\Clusters\SystemSettings\Resources\Imports;

use App\Filament\Clusters\SystemSettings\Resources\Imports\Pages\CreateImport;
use App\Filament\Clusters\SystemSettings\Resources\Imports\Pages\EditImport;
use App\Filament\Clusters\SystemSettings\Resources\Imports\Pages\ListImports;
use App\Filament\Clusters\SystemSettings\Resources\Imports\Pages\ViewImport;
use App\Filament\Clusters\SystemSettings\Resources\Imports\Schemas\ImportForm;
use App\Filament\Clusters\SystemSettings\Resources\Imports\Schemas\ImportInfolist;
use App\Filament\Clusters\SystemSettings\Resources\Imports\Tables\ImportTable;
use App\Filament\Clusters\SystemSettings\SystemSettingsCluster;
use App\Filament\Concerns\UsesCrmAccess;
use App\Models\Import;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ImportResource extends Resource
{
    use UsesCrmAccess;

    protected static ?string $model = Import::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $navigationLabel = '导入任务';

    protected static ?string $modelLabel = '导入任务';

    protected static ?string $pluralModelLabel = '导入任务';

    protected static ?string $title = '导入任务';

    protected static bool $hasTitleCaseModelLabel = false;

    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $cluster = SystemSettingsCluster::class;

    protected static ?string $recordTitleAttribute = 'file_name';

    public static function canCreate(): bool
    {
        return false;
    }

    public static function form(Schema $schema): Schema
    {
        return ImportForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ImportInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ImportTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListImports::route('/'),
            'create' => CreateImport::route('/create'),
            'view' => ViewImport::route('/{record}'),
            'edit' => EditImport::route('/{record}/edit'),
        ];
    }
}

<?php

namespace App\Filament\Clusters\SystemSettings\Resources\FailedImportRows;

use App\Filament\Clusters\SystemSettings\Resources\FailedImportRows\Pages\CreateFailedImportRow;
use App\Filament\Clusters\SystemSettings\Resources\FailedImportRows\Pages\EditFailedImportRow;
use App\Filament\Clusters\SystemSettings\Resources\FailedImportRows\Pages\ListFailedImportRows;
use App\Filament\Clusters\SystemSettings\Resources\FailedImportRows\Pages\ViewFailedImportRow;
use App\Filament\Clusters\SystemSettings\Resources\FailedImportRows\Schemas\FailedImportRowForm;
use App\Filament\Clusters\SystemSettings\Resources\FailedImportRows\Schemas\FailedImportRowInfolist;
use App\Filament\Clusters\SystemSettings\Resources\FailedImportRows\Tables\FailedImportRowTable;
use App\Filament\Clusters\SystemSettings\SystemSettingsCluster;
use App\Filament\Concerns\UsesCrmAccess;
use App\Models\FailedImportRow;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class FailedImportRowResource extends Resource
{
    use UsesCrmAccess;

    protected static ?string $model = FailedImportRow::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $navigationLabel = '导入失败行';

    protected static ?string $modelLabel = '导入失败行';

    protected static ?string $pluralModelLabel = '导入失败行';

    protected static ?string $title = '导入失败行';

    protected static bool $hasTitleCaseModelLabel = false;

    protected static bool $shouldRegisterNavigation = true;

    protected static ?string $cluster = SystemSettingsCluster::class;

    protected static ?string $recordTitleAttribute = 'validation_error';

    public static function form(Schema $schema): Schema
    {
        return FailedImportRowForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return FailedImportRowInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return FailedImportRowTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListFailedImportRows::route('/'),
            'create' => CreateFailedImportRow::route('/create'),
            'view' => ViewFailedImportRow::route('/{record}'),
            'edit' => EditFailedImportRow::route('/{record}/edit'),
        ];
    }
}

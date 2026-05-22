<?php

namespace App\Filament\Clusters\SystemSettings\Resources\DuplicateRecords;

use App\Filament\Clusters\SystemSettings\Resources\DuplicateRecords\Pages\CreateDuplicateRecord;
use App\Filament\Clusters\SystemSettings\Resources\DuplicateRecords\Pages\EditDuplicateRecord;
use App\Filament\Clusters\SystemSettings\Resources\DuplicateRecords\Pages\ListDuplicateRecords;
use App\Filament\Clusters\SystemSettings\Resources\DuplicateRecords\Pages\ViewDuplicateRecord;
use App\Filament\Clusters\SystemSettings\Resources\DuplicateRecords\Schemas\DuplicateRecordForm;
use App\Filament\Clusters\SystemSettings\Resources\DuplicateRecords\Schemas\DuplicateRecordInfolist;
use App\Filament\Clusters\SystemSettings\Resources\DuplicateRecords\Tables\DuplicateRecordTable;
use App\Filament\Clusters\SystemSettings\SystemSettingsCluster;
use App\Filament\Concerns\UsesCrmAccess;
use App\Models\DuplicateRecord;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class DuplicateRecordResource extends Resource
{
    use UsesCrmAccess;

    protected static ?string $model = DuplicateRecord::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedExclamationTriangle;

    protected static ?string $navigationLabel = '疑似重复池';

    protected static ?string $modelLabel = '疑似重复';

    protected static ?string $pluralModelLabel = '疑似重复池';

    protected static ?string $title = '疑似重复池';

    protected static bool $hasTitleCaseModelLabel = false;

    protected static ?string $cluster = SystemSettingsCluster::class;

    protected static ?string $recordTitleAttribute = 'field_value';

    public static function form(Schema $schema): Schema
    {
        return DuplicateRecordForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return DuplicateRecordInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DuplicateRecordTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListDuplicateRecords::route('/'),
            'create' => CreateDuplicateRecord::route('/create'),
            'view' => ViewDuplicateRecord::route('/{record}'),
            'edit' => EditDuplicateRecord::route('/{record}/edit'),
        ];
    }
}

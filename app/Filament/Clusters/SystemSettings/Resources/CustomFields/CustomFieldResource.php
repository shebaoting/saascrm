<?php

namespace App\Filament\Clusters\SystemSettings\Resources\CustomFields;

use App\Filament\Clusters\SystemSettings\Resources\CustomFields\Pages\CreateCustomField;
use App\Filament\Clusters\SystemSettings\Resources\CustomFields\Pages\EditCustomField;
use App\Filament\Clusters\SystemSettings\Resources\CustomFields\Pages\ListCustomFields;
use App\Filament\Clusters\SystemSettings\Resources\CustomFields\Pages\ViewCustomField;
use App\Filament\Clusters\SystemSettings\Resources\CustomFields\Schemas\CustomFieldForm;
use App\Filament\Clusters\SystemSettings\Resources\CustomFields\Schemas\CustomFieldInfolist;
use App\Filament\Clusters\SystemSettings\Resources\CustomFields\Tables\CustomFieldTable;
use App\Filament\Clusters\SystemSettings\SystemSettingsCluster;
use App\Filament\Concerns\UsesCrmAccess;
use App\Models\CustomField;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class CustomFieldResource extends Resource
{
    use UsesCrmAccess;

    protected static ?string $model = CustomField::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $navigationLabel = '字段设置';

    protected static ?string $modelLabel = '字段设置';

    protected static ?string $pluralModelLabel = '字段设置';

    protected static ?string $title = '字段设置';

    protected static bool $hasTitleCaseModelLabel = false;

    protected static ?string $cluster = SystemSettingsCluster::class;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return CustomFieldForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return CustomFieldInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CustomFieldTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCustomFields::route('/'),
            'create' => CreateCustomField::route('/create'),
            'view' => ViewCustomField::route('/{record}'),
            'edit' => EditCustomField::route('/{record}/edit'),
        ];
    }
}

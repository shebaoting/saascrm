<?php

namespace App\Filament\Clusters\SystemSettings\Resources\CustomFieldLayouts;

use App\Filament\Clusters\SystemSettings\Resources\CustomFieldLayouts\Pages\CreateCustomFieldLayout;
use App\Filament\Clusters\SystemSettings\Resources\CustomFieldLayouts\Pages\EditCustomFieldLayout;
use App\Filament\Clusters\SystemSettings\Resources\CustomFieldLayouts\Pages\ListCustomFieldLayouts;
use App\Filament\Clusters\SystemSettings\Resources\CustomFieldLayouts\Pages\ViewCustomFieldLayout;
use App\Filament\Clusters\SystemSettings\Resources\CustomFieldLayouts\Schemas\CustomFieldLayoutForm;
use App\Filament\Clusters\SystemSettings\Resources\CustomFieldLayouts\Schemas\CustomFieldLayoutInfolist;
use App\Filament\Clusters\SystemSettings\Resources\CustomFieldLayouts\Tables\CustomFieldLayoutTable;
use App\Filament\Clusters\SystemSettings\SystemSettingsCluster;
use App\Filament\Concerns\UsesCrmAccess;
use App\Models\CustomFieldLayout;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class CustomFieldLayoutResource extends Resource
{
    use UsesCrmAccess;

    protected static ?string $model = CustomFieldLayout::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $navigationLabel = '字段布局';

    protected static ?string $modelLabel = '字段布局';

    protected static ?string $pluralModelLabel = '字段布局';

    protected static ?string $title = '字段布局';

    protected static bool $hasTitleCaseModelLabel = false;

    protected static bool $shouldRegisterNavigation = true;

    protected static ?string $cluster = SystemSettingsCluster::class;

    protected static ?string $recordTitleAttribute = 'model_type';

    public static function form(Schema $schema): Schema
    {
        return CustomFieldLayoutForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return CustomFieldLayoutInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CustomFieldLayoutTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCustomFieldLayouts::route('/'),
            'create' => CreateCustomFieldLayout::route('/create'),
            'view' => ViewCustomFieldLayout::route('/{record}'),
            'edit' => EditCustomFieldLayout::route('/{record}/edit'),
        ];
    }
}

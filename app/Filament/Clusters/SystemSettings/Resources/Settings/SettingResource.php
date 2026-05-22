<?php

namespace App\Filament\Clusters\SystemSettings\Resources\Settings;

use App\Filament\Clusters\SystemSettings\Resources\Settings\Pages\CreateSetting;
use App\Filament\Clusters\SystemSettings\Resources\Settings\Pages\EditSetting;
use App\Filament\Clusters\SystemSettings\Resources\Settings\Pages\ListSettings;
use App\Filament\Clusters\SystemSettings\Resources\Settings\Pages\ViewSetting;
use App\Filament\Clusters\SystemSettings\Resources\Settings\Schemas\SettingForm;
use App\Filament\Clusters\SystemSettings\Resources\Settings\Schemas\SettingInfolist;
use App\Filament\Clusters\SystemSettings\Resources\Settings\Tables\SettingTable;
use App\Filament\Clusters\SystemSettings\SystemSettingsCluster;
use App\Filament\Concerns\UsesCrmAccess;
use App\Models\Setting;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class SettingResource extends Resource
{
    use UsesCrmAccess;

    protected static ?string $model = Setting::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $navigationLabel = '租户设置';

    protected static ?string $modelLabel = '租户设置';

    protected static ?string $pluralModelLabel = '租户设置';

    protected static ?string $title = '租户设置';

    protected static bool $hasTitleCaseModelLabel = false;

    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $cluster = SystemSettingsCluster::class;

    protected static ?string $recordTitleAttribute = 'key';

    public static function form(Schema $schema): Schema
    {
        return SettingForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return SettingInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SettingTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSettings::route('/'),
            'create' => CreateSetting::route('/create'),
            'view' => ViewSetting::route('/{record}'),
            'edit' => EditSetting::route('/{record}/edit'),
        ];
    }
}

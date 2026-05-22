<?php

namespace App\Filament\Clusters\SystemSettings\Resources\AutomationActions;

use App\Filament\Clusters\SystemSettings\Resources\AutomationActions\Pages\CreateAutomationAction;
use App\Filament\Clusters\SystemSettings\Resources\AutomationActions\Pages\EditAutomationAction;
use App\Filament\Clusters\SystemSettings\Resources\AutomationActions\Pages\ListAutomationActions;
use App\Filament\Clusters\SystemSettings\Resources\AutomationActions\Pages\ViewAutomationAction;
use App\Filament\Clusters\SystemSettings\Resources\AutomationActions\Schemas\AutomationActionForm;
use App\Filament\Clusters\SystemSettings\Resources\AutomationActions\Schemas\AutomationActionInfolist;
use App\Filament\Clusters\SystemSettings\Resources\AutomationActions\Tables\AutomationActionTable;
use App\Filament\Clusters\SystemSettings\SystemSettingsCluster;
use App\Filament\Concerns\UsesCrmAccess;
use App\Models\AutomationAction;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class AutomationActionResource extends Resource
{
    use UsesCrmAccess;

    protected static ?string $model = AutomationAction::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $navigationLabel = '自动化动作';

    protected static ?string $modelLabel = '自动化动作';

    protected static ?string $pluralModelLabel = '自动化动作';

    protected static ?string $title = '自动化动作';

    protected static bool $hasTitleCaseModelLabel = false;

    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $cluster = SystemSettingsCluster::class;

    protected static ?string $recordTitleAttribute = 'action_type';

    public static function form(Schema $schema): Schema
    {
        return AutomationActionForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return AutomationActionInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AutomationActionTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAutomationActions::route('/'),
            'create' => CreateAutomationAction::route('/create'),
            'view' => ViewAutomationAction::route('/{record}'),
            'edit' => EditAutomationAction::route('/{record}/edit'),
        ];
    }
}

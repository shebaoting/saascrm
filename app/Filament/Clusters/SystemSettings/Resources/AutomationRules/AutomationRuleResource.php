<?php

namespace App\Filament\Clusters\SystemSettings\Resources\AutomationRules;

use App\Filament\Clusters\SystemSettings\Resources\AutomationRules\Pages\CreateAutomationRule;
use App\Filament\Clusters\SystemSettings\Resources\AutomationRules\Pages\EditAutomationRule;
use App\Filament\Clusters\SystemSettings\Resources\AutomationRules\Pages\ListAutomationRules;
use App\Filament\Clusters\SystemSettings\Resources\AutomationRules\Pages\ViewAutomationRule;
use App\Filament\Clusters\SystemSettings\Resources\AutomationRules\Schemas\AutomationRuleForm;
use App\Filament\Clusters\SystemSettings\Resources\AutomationRules\Schemas\AutomationRuleInfolist;
use App\Filament\Clusters\SystemSettings\Resources\AutomationRules\Tables\AutomationRuleTable;
use App\Filament\Clusters\SystemSettings\SystemSettingsCluster;
use App\Filament\Concerns\UsesCrmAccess;
use App\Models\AutomationRule;
use App\Services\Crm\PlanLimitService;
use App\Support\CrmAccess;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class AutomationRuleResource extends Resource
{
    use UsesCrmAccess;

    protected static ?string $model = AutomationRule::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $navigationLabel = '自动化规则';

    protected static ?string $modelLabel = '自动化规则';

    protected static ?string $pluralModelLabel = '自动化规则';

    protected static ?string $title = '自动化规则';

    protected static bool $hasTitleCaseModelLabel = false;

    protected static bool $shouldRegisterNavigation = true;

    protected static ?string $cluster = SystemSettingsCluster::class;

    protected static ?string $recordTitleAttribute = 'name';

    public static function canViewAny(): bool
    {
        return app(PlanLimitService::class)->hasFeature(CrmAccess::tenant(), 'automation')
            && CrmAccess::canForModel(static::getModel(), 'viewAny');
    }

    public static function canCreate(): bool
    {
        return app(PlanLimitService::class)->hasFeature(CrmAccess::tenant(), 'automation')
            && CrmAccess::canForModel(static::getModel(), 'create');
    }

    public static function form(Schema $schema): Schema
    {
        return AutomationRuleForm::configure($schema, static::class);
    }

    public static function infolist(Schema $schema): Schema
    {
        return AutomationRuleInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AutomationRuleTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAutomationRules::route('/'),
            'create' => CreateAutomationRule::route('/create'),
            'view' => ViewAutomationRule::route('/{record}'),
            'edit' => EditAutomationRule::route('/{record}/edit'),
        ];
    }

    public static function conditionFieldOptions(): array
    {
        return [
            'source' => '来源',
            'status' => '状态',
            'owner_user_id' => '负责人',
            'customer_type' => '客户类型',
            'lifecycle_stage' => '客户阶段',
            'forecast_category' => '预测分类',
            'amount' => '金额',
            'type' => '活动类型',
        ];
    }

    public static function operatorOptions(): array
    {
        return [
            '=' => '等于',
            '!=' => '不等于',
            'contains' => '包含',
            'filled' => '已填写',
            'blank' => '未填写',
        ];
    }
}

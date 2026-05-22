<?php

namespace App\Filament\Clusters\LeadCenter\Resources\LeadScoreRules;

use App\Filament\Clusters\LeadCenter\LeadCenterCluster;
use App\Filament\Clusters\LeadCenter\Resources\LeadScoreRules\Pages\CreateLeadScoreRule;
use App\Filament\Clusters\LeadCenter\Resources\LeadScoreRules\Pages\EditLeadScoreRule;
use App\Filament\Clusters\LeadCenter\Resources\LeadScoreRules\Pages\ListLeadScoreRules;
use App\Filament\Clusters\LeadCenter\Resources\LeadScoreRules\Pages\ViewLeadScoreRule;
use App\Filament\Clusters\LeadCenter\Resources\LeadScoreRules\Schemas\LeadScoreRuleForm;
use App\Filament\Clusters\LeadCenter\Resources\LeadScoreRules\Schemas\LeadScoreRuleInfolist;
use App\Filament\Clusters\LeadCenter\Resources\LeadScoreRules\Tables\LeadScoreRuleTable;
use App\Filament\Concerns\UsesCrmAccess;
use App\Models\LeadScoreRule;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class LeadScoreRuleResource extends Resource
{
    use UsesCrmAccess;

    protected static ?string $model = LeadScoreRule::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $navigationLabel = '线索评分规则';

    protected static ?string $modelLabel = '线索评分规则';

    protected static ?string $pluralModelLabel = '线索评分规则';

    protected static ?string $title = '线索评分规则';

    protected static bool $hasTitleCaseModelLabel = false;

    protected static bool $shouldRegisterNavigation = true;

    protected static ?string $cluster = LeadCenterCluster::class;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return LeadScoreRuleForm::configure($schema, static::class);
    }

    public static function infolist(Schema $schema): Schema
    {
        return LeadScoreRuleInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return LeadScoreRuleTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListLeadScoreRules::route('/'),
            'create' => CreateLeadScoreRule::route('/create'),
            'view' => ViewLeadScoreRule::route('/{record}'),
            'edit' => EditLeadScoreRule::route('/{record}/edit'),
        ];
    }

    public static function fieldOptions(): array
    {
        return [
            'source' => '来源',
            'area_id' => '地区',
            'industry' => '行业',
            'company_name' => '公司名称',
            'contact_name' => '联系人',
            'phone' => '手机号',
            'email' => '邮箱',
            'score' => '当前分数',
            'status' => '状态',
            'custom_fields.industry' => '自定义字段：行业',
            'custom_fields.product_line' => '自定义字段：产品线',
            'behavior.activity_count' => '行为：跟进次数',
            'behavior.has_follow_up' => '行为：有下次跟进',
            'behavior.days_since_last_activity' => '行为：距上次跟进天数',
            'completeness.percent' => '资料完整度',
        ];
    }

    public static function operatorOptions(): array
    {
        return [
            'eq' => '等于',
            'neq' => '不等于',
            'contains' => '包含',
            'in' => '属于任一',
            'not_in' => '不属于',
            'not_empty' => '已填写',
            'empty' => '未填写',
            'gt' => '大于',
            'gte' => '大于等于',
            'lt' => '小于',
            'lte' => '小于等于',
            'between' => '介于两值之间',
        ];
    }
}

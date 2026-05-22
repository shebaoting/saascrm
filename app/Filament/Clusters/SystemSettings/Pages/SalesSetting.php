<?php

namespace App\Filament\Clusters\SystemSettings\Pages;

use App\Filament\Clusters\CustomerCenter\Resources\CustomerPoolRules\CustomerPoolRuleResource;
use App\Filament\Clusters\LeadCenter\Resources\AssignmentRules\AssignmentRuleResource;
use App\Filament\Clusters\LeadCenter\Resources\LeadScoreRules\LeadScoreRuleResource;
use App\Filament\Clusters\SalesProcess\Resources\Pipelines\PipelineResource;
use App\Filament\Clusters\SalesProcess\Resources\PipelineStages\PipelineStageResource;
use App\Filament\Clusters\SystemSettings\Resources\AutomationRules\AutomationRuleResource;
use App\Filament\Clusters\SystemSettings\Resources\BusinessNumberRules\BusinessNumberRuleResource;
use App\Filament\Clusters\SystemSettings\SystemSettingsCluster;
use App\Models\Setting;
use App\Models\User;
use App\Services\Crm\PlanLimitService;
use App\Support\CrmAccess;
use App\Support\CrmMetrics;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class SalesSetting extends Page
{
    protected string $view = 'filament.pages.crm-metric-page';

    protected static ?string $cluster = SystemSettingsCluster::class;

    protected static ?string $navigationLabel = '销售设置';

    protected static ?string $title = '销售设置';

    protected static ?int $navigationSort = 90;

    protected function getViewData(): array
    {
        return CrmMetrics::salesSetting();
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('pipelines')
                ->label('管道设置')
                ->icon('heroicon-o-adjustments-horizontal')
                ->url(PipelineResource::getUrl()),
            Action::make('pipeline_stages')
                ->label('阶段设置')
                ->icon('heroicon-o-queue-list')
                ->url(PipelineStageResource::getUrl()),
            Action::make('pool_rules')
                ->label('公海规则')
                ->icon('heroicon-o-archive-box')
                ->url(CustomerPoolRuleResource::getUrl()),
            Action::make('assignment_rules')
                ->label('分配规则')
                ->icon('heroicon-o-users')
                ->url(AssignmentRuleResource::getUrl()),
            Action::make('score_rules')
                ->label('评分规则')
                ->icon('heroicon-o-star')
                ->url(LeadScoreRuleResource::getUrl()),
            Action::make('automation_rules')
                ->label('自动化规则')
                ->icon('heroicon-o-bolt')
                ->disabled(fn (): bool => ! app(PlanLimitService::class)->hasFeature(CrmAccess::tenant(), 'automation'))
                ->tooltip(fn (): ?string => app(PlanLimitService::class)->hasFeature(CrmAccess::tenant(), 'automation')
                    ? null
                    : '当前套餐不包含自动化规则，请升级后使用。')
                ->url(AutomationRuleResource::getUrl()),
            Action::make('business_number_rules')
                ->label('编号规则')
                ->icon('heroicon-o-hashtag')
                ->url(BusinessNumberRuleResource::getUrl()),
            Action::make('duplicate_rules')
                ->label('重复规则')
                ->icon('heroicon-o-finger-print')
                ->fillForm(function (): array {
                    $value = Setting::query()
                        ->where('tenant_id', CrmAccess::tenantId())
                        ->where('key', 'duplicate_rules')
                        ->value('value');

                    return [
                        'phone_exact' => data_get($value, 'phone_exact', true),
                        'email_exact' => data_get($value, 'email_exact', true),
                        'company_name_fuzzy' => data_get($value, 'company_name_fuzzy', true),
                        'unified_social_credit_code_exact' => data_get($value, 'unified_social_credit_code_exact', true),
                    ];
                })
                ->form([
                    Toggle::make('phone_exact')
                        ->label('手机号精确匹配')
                        ->default(true),
                    Toggle::make('email_exact')
                        ->label('邮箱精确匹配')
                        ->default(true),
                    Toggle::make('company_name_fuzzy')
                        ->label('客户名称模糊匹配')
                        ->default(true),
                    Toggle::make('unified_social_credit_code_exact')
                        ->label('统一社会信用代码精确匹配')
                        ->default(true),
                ])
                ->action(function (array $data): void {
                    Setting::updateOrCreate(
                        ['tenant_id' => CrmAccess::tenantId(), 'key' => 'duplicate_rules'],
                        ['value' => [
                            'phone_exact' => (bool) ($data['phone_exact'] ?? false),
                            'email_exact' => (bool) ($data['email_exact'] ?? false),
                            'company_name_fuzzy' => (bool) ($data['company_name_fuzzy'] ?? false),
                            'unified_social_credit_code_exact' => (bool) ($data['unified_social_credit_code_exact'] ?? false),
                        ]],
                    );

                    Notification::make()->success()->title('重复规则已保存')->send();
                }),
            Action::make('quote_approval_rules')
                ->label('报价审批规则')
                ->icon('heroicon-o-shield-check')
                ->fillForm(function (): array {
                    $value = Setting::query()
                        ->where('tenant_id', CrmAccess::tenantId())
                        ->where('key', 'quote_approval_rules')
                        ->value('value');

                    return is_array($value) ? $value : [];
                })
                ->form([
                    TextInput::make('min_profit_margin')
                        ->label('最低毛利率(%)')
                        ->numeric(),
                    TextInput::make('max_discount_rate')
                        ->label('最高折扣率(%)')
                        ->numeric(),
                    TextInput::make('max_amount_without_approval')
                        ->label('免审金额上限')
                        ->numeric(),
                    Select::make('approver_id')
                        ->label('默认审批人')
                        ->options(fn (): array => User::query()
                            ->whereHas('tenants', fn ($query) => $query->whereKey(CrmAccess::tenantId()))
                            ->orderBy('name')
                            ->pluck('name', 'id')
                            ->all()),
                ])
                ->action(function (array $data): void {
                    Setting::updateOrCreate(
                        ['tenant_id' => CrmAccess::tenantId(), 'key' => 'quote_approval_rules'],
                        ['value' => array_filter($data, fn (mixed $value): bool => filled($value) || $value === 0 || $value === '0')],
                    );

                    Notification::make()->success()->title('报价审批规则已保存')->send();
                }),
        ];
    }
}

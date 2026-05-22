<?php

namespace App\Support;

use App\Models\Activity;
use App\Models\AssignmentRule;
use App\Models\AuditLog;
use App\Models\Customer;
use App\Models\Department;
use App\Models\Export;
use App\Models\FailedImportRow;
use App\Models\Import;
use App\Models\Lead;
use App\Models\LeadScoreRule;
use App\Models\Opportunity;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Pipeline;
use App\Models\PipelineStage;
use App\Models\Plan;
use App\Models\Quote;
use App\Models\SalesTarget;
use App\Models\Task;
use App\Models\Tenant;
use App\Models\User;
use App\Support\Filament\CrmUi;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Auth;

class CrmMetrics
{
    public static function salesWorkspace(): array
    {
        $tenantId = self::tenantId();
        $userId = Auth::id();

        return [
            'heading' => '销售工作台',
            'description' => '今天需要优先处理的线索、任务和商机。',
            'metrics' => [
                ['label' => '我的线索', 'value' => Lead::where('tenant_id', $tenantId)->where('owner_user_id', $userId)->count()],
                ['label' => '逾期任务', 'value' => Task::where('tenant_id', $tenantId)->where('assignee_id', $userId)->where('status', '!=', 'completed')->where('due_at', '<', now())->count()],
                ['label' => '本月商机', 'value' => self::openOpportunityValue($tenantId)],
                ['label' => '待收款', 'value' => self::money(Order::where('tenant_id', $tenantId)->where('payment_status', '!=', 'paid')->sum('total_amount'))],
            ],
            'rows' => Task::where('tenant_id', $tenantId)
                ->where('assignee_id', $userId)
                ->where('status', '!=', 'completed')
                ->orderBy('due_at')
                ->limit(8)
                ->get(['title', 'status', 'due_at'])
                ->map(fn (Task $task): array => [
                    'title' => $task->title,
                    'meta' => self::optionLabel('task.status', $task->status),
                    'value' => $task->due_at?->format('Y-m-d H:i') ?? '-',
                ])
                ->all(),
        ];
    }

    public static function managementDashboard(): array
    {
        $tenantId = self::tenantId();

        return [
            'heading' => '管理驾驶舱',
            'description' => '销售结果、预测、目标和回款概览。',
            'metrics' => [
                ['label' => '客户数', 'value' => Customer::where('tenant_id', $tenantId)->count()],
                ['label' => '报价金额', 'value' => self::money(Quote::where('tenant_id', $tenantId)->sum('total_amount'))],
                ['label' => '订单金额', 'value' => self::money(Order::where('tenant_id', $tenantId)->sum('total_amount'))],
                ['label' => '已收款', 'value' => self::money(Payment::where('tenant_id', $tenantId)->where('status', 'completed')->sum('amount'))],
            ],
            'rows' => SalesTarget::where('tenant_id', $tenantId)
                ->latest('period_start')
                ->limit(6)
                ->get()
                ->map(fn (SalesTarget $target): array => [
                    'title' => self::salesTargetName($target),
                    'meta' => $target->period_start?->format('Y-m-d').' - '.$target->period_end?->format('Y-m-d'),
                    'value' => self::money($target->target_amount),
                ])
                ->all(),
        ];
    }

    public static function calendar(): array
    {
        $tenantId = self::tenantId();

        return [
            'heading' => '日历',
            'description' => '近期任务和下一次跟进安排。',
            'metrics' => [
                ['label' => '今日任务', 'value' => Task::where('tenant_id', $tenantId)->whereDate('due_at', today())->count()],
                ['label' => '本周任务', 'value' => Task::where('tenant_id', $tenantId)->whereBetween('due_at', [now()->startOfWeek(), now()->endOfWeek()])->count()],
                ['label' => '今日活动', 'value' => Activity::where('tenant_id', $tenantId)->whereDate('occurred_at', today())->count()],
                ['label' => '下次跟进', 'value' => Activity::where('tenant_id', $tenantId)->whereNotNull('next_follow_at')->count()],
            ],
            'rows' => Task::where('tenant_id', $tenantId)
                ->whereNotNull('due_at')
                ->orderBy('due_at')
                ->limit(10)
                ->get(['title', 'priority', 'due_at'])
                ->map(fn (Task $task): array => [
                    'title' => $task->title,
                    'meta' => self::optionLabel('task.priority', $task->priority),
                    'value' => $task->due_at?->format('m-d H:i') ?? '-',
                ])
                ->all(),
        ];
    }

    public static function pipelineReport(): array
    {
        $tenantId = self::tenantId();

        return [
            'heading' => '销售漏斗',
            'description' => '按阶段查看商机数量、金额和概率。',
            'metrics' => [
                ['label' => '管道数', 'value' => Pipeline::where('tenant_id', $tenantId)->count()],
                ['label' => '阶段数', 'value' => PipelineStage::where('tenant_id', $tenantId)->count()],
                ['label' => '打开商机金额', 'value' => self::money(self::openOpportunityValue($tenantId))],
                ['label' => '成交订单金额', 'value' => self::money(Order::where('tenant_id', $tenantId)->where('order_status', 'completed')->sum('total_amount'))],
            ],
            'rows' => PipelineStage::where('tenant_id', $tenantId)
                ->withCount('opportunities')
                ->orderBy('sort_order')
                ->get()
                ->map(fn (PipelineStage $stage): array => [
                    'title' => $stage->name,
                    'meta' => $stage->probability.'%',
                    'value' => $stage->opportunities_count.' 个商机',
                ])
                ->all(),
        ];
    }

    public static function forecastReport(): array
    {
        $tenantId = self::tenantId();

        return [
            'heading' => '销售预测',
            'description' => '按管道预测、最佳情况、承诺成交和已成交分类汇总。',
            'metrics' => collect(['pipeline', 'best_case', 'commit', 'closed'])
                ->map(fn (string $category): array => [
                    'label' => self::optionLabel('forecast_category', $category),
                    'value' => self::money(Opportunity::where('tenant_id', $tenantId)->where('forecast_category', $category)->sum('amount')),
                ])
                ->all(),
            'rows' => Opportunity::where('tenant_id', $tenantId)
                ->orderBy('expected_close_date')
                ->limit(10)
                ->get(['name', 'forecast_category', 'amount'])
                ->map(fn ($opportunity): array => [
                    'title' => $opportunity->name,
                    'meta' => self::optionLabel('forecast_category', $opportunity->forecast_category),
                    'value' => self::money($opportunity->amount),
                ])
                ->all(),
        ];
    }

    public static function activityReport(): array
    {
        $tenantId = self::tenantId();

        return [
            'heading' => '跟进效率',
            'description' => '活动数、任务状态和逾期情况。',
            'metrics' => [
                ['label' => '活动总数', 'value' => Activity::where('tenant_id', $tenantId)->count()],
                ['label' => '电话', 'value' => Activity::where('tenant_id', $tenantId)->where('type', 'call')->count()],
                ['label' => '待办任务', 'value' => Task::where('tenant_id', $tenantId)->whereIn('status', ['not_started', 'in_progress'])->count()],
                ['label' => '逾期任务', 'value' => Task::where('tenant_id', $tenantId)->where('status', '!=', 'completed')->where('due_at', '<', now())->count()],
            ],
            'rows' => Activity::where('tenant_id', $tenantId)
                ->latest('occurred_at')
                ->limit(10)
                ->get(['subject', 'type', 'occurred_at'])
                ->map(fn (Activity $activity): array => [
                    'title' => $activity->subject ?: '未命名活动',
                    'meta' => self::optionLabel('activity.type', $activity->type),
                    'value' => $activity->occurred_at?->format('m-d H:i') ?? '-',
                ])
                ->all(),
        ];
    }

    public static function financeReport(): array
    {
        $tenantId = self::tenantId();

        return [
            'heading' => '财务统计',
            'description' => '订单、回款、支出和毛利。',
            'metrics' => [
                ['label' => '订单额', 'value' => self::money(Order::where('tenant_id', $tenantId)->sum('total_amount'))],
                ['label' => '已收款', 'value' => self::money(Payment::where('tenant_id', $tenantId)->where('status', 'completed')->sum('amount'))],
                ['label' => '待收款', 'value' => self::money(Order::where('tenant_id', $tenantId)->where('payment_status', '!=', 'paid')->sum('total_amount'))],
                ['label' => '毛利', 'value' => self::money(Order::where('tenant_id', $tenantId)->sum('gross_profit'))],
            ],
            'rows' => Order::where('tenant_id', $tenantId)
                ->latest('ordered_at')
                ->limit(10)
                ->get(['order_number', 'payment_status', 'total_amount'])
                ->map(fn (Order $order): array => [
                    'title' => $order->order_number,
                    'meta' => self::optionLabel('payment_status', $order->payment_status),
                    'value' => self::money($order->total_amount),
                ])
                ->all(),
        ];
    }

    public static function salesSetting(): array
    {
        $tenantId = self::tenantId();

        return [
            'heading' => '销售设置',
            'description' => '管道、公海、分配、评分和自动化规则概览。',
            'metrics' => [
                ['label' => '销售管道', 'value' => Pipeline::where('tenant_id', $tenantId)->count()],
                ['label' => '管道阶段', 'value' => PipelineStage::where('tenant_id', $tenantId)->count()],
                ['label' => '线索评分规则', 'value' => LeadScoreRule::where('tenant_id', $tenantId)->count()],
                ['label' => '分配规则', 'value' => AssignmentRule::where('tenant_id', $tenantId)->count()],
            ],
            'rows' => Pipeline::where('tenant_id', $tenantId)
                ->withCount('stages')
                ->orderByDesc('is_default')
                ->get()
                ->map(fn (Pipeline $pipeline): array => [
                    'title' => $pipeline->name,
                    'meta' => $pipeline->is_active ? '启用' : '停用',
                    'value' => $pipeline->stages_count.' 个阶段',
                ])
                ->all(),
        ];
    }

    public static function importExport(): array
    {
        $tenantId = self::tenantId();

        return [
            'heading' => '导入导出',
            'description' => '导入任务、导出任务和失败行追踪。',
            'metrics' => [
                ['label' => '导入任务', 'value' => Import::where('tenant_id', $tenantId)->count()],
                ['label' => '导出任务', 'value' => Export::where('tenant_id', $tenantId)->count()],
                ['label' => '失败行', 'value' => FailedImportRow::where('tenant_id', $tenantId)->count()],
                ['label' => '已完成导入', 'value' => Import::where('tenant_id', $tenantId)->whereNotNull('completed_at')->count()],
            ],
            'rows' => Import::where('tenant_id', $tenantId)
                ->latest()
                ->limit(10)
                ->get(['file_name', 'processed_rows', 'successful_rows'])
                ->map(fn (Import $import): array => [
                    'title' => $import->file_name,
                    'meta' => $import->processed_rows.' 行',
                    'value' => $import->successful_rows.' 成功',
                ])
                ->all(),
        ];
    }

    public static function platformOverview(): array
    {
        return [
            'heading' => '平台概览',
            'description' => '租户、套餐、用量和审计概览。',
            'metrics' => [
                ['label' => '租户数', 'value' => Tenant::count()],
                ['label' => '活跃租户', 'value' => Tenant::where('status', 'active')->orWhere('status', 'trial')->count()],
                ['label' => '套餐数', 'value' => Plan::count()],
                ['label' => '审计日志', 'value' => AuditLog::count()],
            ],
            'rows' => Tenant::latest()
                ->limit(10)
                ->get(['name', 'status', 'created_at'])
                ->map(fn (Tenant $tenant): array => [
                    'title' => $tenant->name,
                    'meta' => self::optionLabel('tenant.status', $tenant->status),
                    'value' => $tenant->created_at?->format('Y-m-d') ?? '-',
                ])
                ->all(),
        ];
    }

    private static function tenantId(): ?int
    {
        return Filament::getTenant()?->getKey();
    }

    private static function money(mixed $amount): string
    {
        return '¥'.number_format((float) $amount, 2);
    }

    private static function optionLabel(string $key, mixed $value): string
    {
        return CrmUi::options($key)[(string) $value] ?? (string) $value;
    }

    private static function salesTargetName(SalesTarget $target): string
    {
        return match ($target->target_type) {
            'tenant' => '全公司目标',
            'department' => '部门目标：'.(Department::query()
                ->where('tenant_id', $target->tenant_id)
                ->whereKey($target->target_id)
                ->value('name') ?? '#'.$target->target_id),
            'user' => '员工目标：'.(User::query()
                ->whereKey($target->target_id)
                ->value('name') ?? '#'.$target->target_id),
            default => self::optionLabel('target_type', $target->target_type).' #'.($target->target_id ?: '-'),
        };
    }

    private static function openOpportunityValue(?int $tenantId): float
    {
        return (float) Opportunity::where('tenant_id', $tenantId)
            ->whereNull('ended_at')
            ->sum('amount');
    }
}

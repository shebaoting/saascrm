<?php

namespace App\Support;

use App\Models\Activity;
use App\Models\AssignmentRule;
use App\Models\Customer;
use App\Models\CustomerPoolHistory;
use App\Models\Department;
use App\Models\Export;
use App\Models\FailedImportRow;
use App\Models\Import;
use App\Models\Lead;
use App\Models\LeadScoreRule;
use App\Models\Opportunity;
use App\Models\OpportunityStageHistory;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Pipeline;
use App\Models\PipelineStage;
use App\Models\Plan;
use App\Models\Quote;
use App\Models\SalesTarget;
use App\Models\Task;
use App\Models\Tenant;
use App\Models\TenantSubscription;
use App\Models\User;
use App\Services\Crm\PlanLimitService;
use App\Support\Filament\CrmUi;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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
                ['label' => '今日跟进', 'value' => Activity::where('tenant_id', $tenantId)->whereDate('occurred_at', today())->count()],
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
        $totalOpen = max(1, Opportunity::where('tenant_id', $tenantId)->whereNull('ended_at')->count());
        $lostReasons = Opportunity::query()
            ->where('tenant_id', $tenantId)
            ->whereNotNull('lost_reason')
            ->select('lost_reason', DB::raw('count(*) as aggregate'))
            ->groupBy('lost_reason')
            ->orderByDesc('aggregate')
            ->limit(3)
            ->pluck('aggregate', 'lost_reason')
            ->map(fn ($count, string $reason): string => $reason.' '.$count.' 个')
            ->values()
            ->join('，');

        return [
            'heading' => '销售漏斗',
            'description' => '按阶段查看商机数量、金额、转化率、平均停留天数和阶段流失。',
            'metrics' => [
                ['label' => '管道数', 'value' => Pipeline::where('tenant_id', $tenantId)->count()],
                ['label' => '阶段数', 'value' => PipelineStage::where('tenant_id', $tenantId)->count()],
                ['label' => '打开商机金额', 'value' => self::money(self::openOpportunityValue($tenantId))],
                ['label' => '阶段流失原因', 'value' => $lostReasons ?: '-'],
            ],
            'rows' => PipelineStage::where('tenant_id', $tenantId)
                ->withCount('opportunities')
                ->orderBy('sort_order')
                ->get()
                ->map(function (PipelineStage $stage) use ($tenantId, $totalOpen): array {
                    $amount = Opportunity::where('tenant_id', $tenantId)->where('pipeline_stage_id', $stage->id)->sum('amount');
                    $avgStay = OpportunityStageHistory::where('tenant_id', $tenantId)
                        ->where('to_stage_id', $stage->id)
                        ->get('changed_at')
                        ->avg(fn (OpportunityStageHistory $history): int => max(0, (int) $history->changed_at?->diffInDays(now())));

                    return [
                        'title' => $stage->name,
                        'meta' => $stage->opportunities_count.' 个 / '.self::money($amount).' / 平均停留 '.round((float) $avgStay, 1).' 天',
                        'value' => self::percent($stage->opportunities_count, $totalOpen),
                    ];
                })
                ->all(),
        ];
    }

    public static function forecastReport(): array
    {
        $tenantId = self::tenantId();
        $start = now()->startOfQuarter();
        $end = now()->endOfQuarter();

        return [
            'heading' => '销售预测',
            'description' => '本季度按 commit、best case、pipeline 和负责人维度预测预计成交金额。',
            'metrics' => collect(['pipeline', 'best_case', 'commit', 'closed'])
                ->map(fn (string $category): array => [
                    'label' => self::optionLabel('forecast_category', $category),
                    'value' => self::money(Opportunity::where('tenant_id', $tenantId)
                        ->where('forecast_category', $category)
                        ->whereBetween('expected_close_date', [$start, $end])
                        ->sum('amount')),
                ])
                ->all(),
            'rows' => Opportunity::where('tenant_id', $tenantId)
                ->whereBetween('expected_close_date', [$start, $end])
                ->with('responsible')
                ->orderBy('expected_close_date')
                ->limit(20)
                ->get(['name', 'forecast_category', 'amount', 'responsible_user_id', 'expected_close_date'])
                ->map(fn ($opportunity): array => [
                    'title' => $opportunity->name,
                    'meta' => self::optionLabel('forecast_category', $opportunity->forecast_category).' / '.($opportunity->responsible?->name ?: '-').' / '.($opportunity->expected_close_date?->format('Y-m-d') ?: '-'),
                    'value' => self::money($opportunity->amount),
                ])
                ->all(),
        ];
    }

    public static function activityReport(): array
    {
        $tenantId = self::tenantId();
        $firstResponseHours = Activity::query()
            ->where('tenant_id', $tenantId)
            ->whereNotNull('lead_id')
            ->with('lead:id,created_at')
            ->get()
            ->map(fn (Activity $activity): ?int => $activity->lead?->created_at ? $activity->lead->created_at->diffInHours($activity->occurred_at) : null)
            ->filter(fn ($hours): bool => $hours !== null)
            ->avg();

        return [
            'heading' => '跟进效率',
            'description' => '首次响应、未跟进客户、逾期任务、跟进频率和员工排行。',
            'metrics' => [
                ['label' => '平均首次响应', 'value' => $firstResponseHours === null ? '-' : round((float) $firstResponseHours, 1).' 小时'],
                ['label' => '30天未跟进客户', 'value' => Customer::where('tenant_id', $tenantId)->where(fn ($query) => $query->whereNull('last_activity_at')->orWhere('last_activity_at', '<', now()->subDays(30)))->count()],
                ['label' => '逾期任务', 'value' => Task::where('tenant_id', $tenantId)->where('status', '!=', 'completed')->where('due_at', '<', now())->count()],
                ['label' => '本周跟进', 'value' => Activity::where('tenant_id', $tenantId)->whereBetween('occurred_at', [now()->startOfWeek(), now()->endOfWeek()])->count()],
            ],
            'rows' => User::query()
                ->whereHas('tenants', fn ($query) => $query->whereKey($tenantId))
                ->get()
                ->map(fn (User $user): array => [
                    'title' => $user->name,
                    'meta' => '跟进 '.Activity::where('tenant_id', $tenantId)->where('owner_user_id', $user->id)->count().' 次 / 逾期任务 '.Task::where('tenant_id', $tenantId)->where('assignee_id', $user->id)->where('status', '!=', 'completed')->where('due_at', '<', now())->count().' 个',
                    'value' => '客户 '.Customer::where('tenant_id', $tenantId)->where('owner_user_id', $user->id)->where(fn ($query) => $query->whereNull('last_activity_at')->orWhere('last_activity_at', '<', now()->subDays(30)))->count().' 未跟进',
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

    public static function salesTargetReport(): array
    {
        $tenantId = self::tenantId();
        $start = now()->startOfMonth();
        $end = now()->endOfMonth();
        $targets = SalesTarget::where('tenant_id', $tenantId)
            ->where('period_start', '<=', $end)
            ->where('period_end', '>=', $start)
            ->get();
        $targetAmount = (float) $targets->sum('target_amount');
        $orderAmount = (float) Order::where('tenant_id', $tenantId)->whereBetween('ordered_at', [$start, $end])->sum('total_amount');
        $paymentAmount = (float) Payment::where('tenant_id', $tenantId)->where('status', 'completed')->whereBetween('received_at', [$start, $end])->sum('amount');

        return [
            'heading' => '目标完成',
            'description' => '按员工、部门和全公司统计目标金额、已成交、已回款、新增客户和完成率。',
            'metrics' => [
                ['label' => '目标金额', 'value' => self::money($targetAmount)],
                ['label' => '已成交', 'value' => self::money($orderAmount)],
                ['label' => '已回款', 'value' => self::money($paymentAmount)],
                ['label' => '成交完成率', 'value' => self::percent($orderAmount, max(1, $targetAmount))],
            ],
            'rows' => $targets
                ->map(function (SalesTarget $target) use ($tenantId): array {
                    $achieved = self::targetOrderAmount($tenantId, $target);
                    $paid = self::targetPaymentAmount($tenantId, $target);
                    $newCustomers = self::targetNewCustomerCount($tenantId, $target);

                    return [
                        'title' => self::salesTargetName($target),
                        'meta' => '成交 '.self::money($achieved).' / 回款 '.self::money($paid).' / 新增客户 '.$newCustomers,
                        'value' => self::percent($achieved, (float) $target->target_amount ?: 1),
                    ];
                })
                ->values()
                ->all(),
        ];
    }

    public static function leadConversionReport(): array
    {
        $tenantId = self::tenantId();
        $total = Lead::where('tenant_id', $tenantId)->count();
        $converted = Lead::where('tenant_id', $tenantId)->where('status', 'converted')->count();

        return [
            'heading' => '线索转化',
            'description' => '按来源、评分区间、负责人统计转客户率、无效率和转化周期。',
            'metrics' => [
                ['label' => '线索总数', 'value' => $total],
                ['label' => '已转客户', 'value' => $converted],
                ['label' => '转化率', 'value' => self::percent($converted, max(1, $total))],
                ['label' => '无效率', 'value' => self::percent(Lead::where('tenant_id', $tenantId)->whereIn('status', ['invalid', 'lost'])->count(), max(1, $total))],
            ],
            'rows' => Lead::where('tenant_id', $tenantId)
                ->select('source', DB::raw('count(*) as total'), DB::raw("sum(case when status = 'converted' then 1 else 0 end) as converted"), DB::raw("sum(case when status in ('invalid', 'lost') then 1 else 0 end) as invalid_count"))
                ->groupBy('source')
                ->orderByDesc('converted')
                ->get()
                ->map(fn ($row): array => [
                    'title' => $row->source ?: '未填写来源',
                    'meta' => '转客户 '.$row->converted.' / 无效 '.$row->invalid_count.' / 总数 '.$row->total,
                    'value' => self::percent((int) $row->converted, max(1, (int) $row->total)),
                ])
                ->all(),
        ];
    }

    public static function poolReport(): array
    {
        $tenantId = self::tenantId();
        $entered = CustomerPoolHistory::where('tenant_id', $tenantId)->where('action', 'release')->count();
        $claimed = CustomerPoolHistory::where('tenant_id', $tenantId)->where('action', 'claim')->count();

        return [
            'heading' => '公海效率',
            'description' => '查看进入公海、领取、转化、回收原因和领取后的成交价值。',
            'metrics' => [
                ['label' => '进入公海', 'value' => $entered],
                ['label' => '领取数', 'value' => $claimed],
                ['label' => '领取率', 'value' => self::percent($claimed, max(1, $entered))],
                ['label' => '领取后成交额', 'value' => self::money(Order::where('tenant_id', $tenantId)->whereNotNull('employee_id')->sum('total_amount'))],
            ],
            'rows' => CustomerPoolHistory::where('tenant_id', $tenantId)
                ->where('action', 'release')
                ->select('reason', DB::raw('count(*) as aggregate'))
                ->groupBy('reason')
                ->orderByDesc('aggregate')
                ->get()
                ->map(fn ($row): array => [
                    'title' => $row->reason ?: '未填写原因',
                    'meta' => '回收原因',
                    'value' => $row->aggregate.' 次',
                ])
                ->all(),
        ];
    }

    public static function productSalesReport(): array
    {
        $tenantId = self::tenantId();
        $start = now()->startOfMonth();
        $end = now()->endOfMonth();
        $items = OrderItem::where('order_items.tenant_id', $tenantId)
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->whereBetween('orders.ordered_at', [$start, $end])
            ->select(
                'order_items.sku_code',
                'order_items.product_name',
                DB::raw('sum(order_items.quantity) as quantity'),
                DB::raw('sum(order_items.subtotal_amount) as sales_amount'),
                DB::raw('sum(order_items.subtotal_amount - order_items.cost_price * order_items.quantity) as gross_profit'),
            )
            ->groupBy('order_items.sku_code', 'order_items.product_name')
            ->orderByDesc('sales_amount')
            ->get();

        return [
            'heading' => '商品销售',
            'description' => '本月 SKU 销量、销售额、毛利和订单明细汇总。',
            'metrics' => [
                ['label' => 'SKU 数', 'value' => $items->count()],
                ['label' => '销售额最高', 'value' => $items->first()?->sku_code ?: '-'],
                ['label' => '毛利最高', 'value' => $items->sortByDesc('gross_profit')->first()?->sku_code ?: '-'],
                ['label' => '本月毛利', 'value' => self::money($items->sum('gross_profit'))],
            ],
            'rows' => $items
                ->map(fn ($row): array => [
                    'title' => ($row->sku_code ?: '未记录 SKU').' '.$row->product_name,
                    'meta' => '销量 '.$row->quantity.' / 毛利 '.self::money($row->gross_profit),
                    'value' => self::money($row->sales_amount),
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
        $activeTenantIds = Tenant::query()
            ->whereIn('status', ['active', 'trial'])
            ->pluck('id');
        $mrr = TenantSubscription::query()
            ->whereIn('tenant_id', $activeTenantIds)
            ->whereIn('status', ['active', 'trialing'])
            ->with('plan')
            ->get()
            ->sum(fn (TenantSubscription $subscription): float => (float) ($subscription->plan?->price_monthly ?? 0));
        $expiringSubscriptions = TenantSubscription::query()
            ->whereIn('status', ['active', 'trialing'])
            ->whereBetween('ends_at', [now(), now()->addDays(7)])
            ->with(['plan', 'tenant'])
            ->limit(5)
            ->get();
        $planRows = Plan::query()
            ->withCount('subscriptions')
            ->orderByDesc('subscriptions_count')
            ->limit(5)
            ->get()
            ->map(fn (Plan $plan): array => [
                'title' => '套餐分布：'.$plan->name,
                'meta' => $plan->code,
                'value' => $plan->subscriptions_count.' 个租户',
            ]);
        $usageRows = Tenant::query()
            ->whereIn('id', $activeTenantIds)
            ->with('activeSubscription.plan')
            ->limit(20)
            ->get()
            ->map(function (Tenant $tenant): ?array {
                $storage = app(PlanLimitService::class)->storageStatus($tenant);

                if (! $storage['exceeded'] && ($storage['percent'] ?? 0) < 80) {
                    return null;
                }

                return [
                    'title' => '用量异常：'.$tenant->name,
                    'meta' => '存储 '.$storage['used_mb'].'MB / '.($storage['limit_mb'] ?? '不限').'MB',
                    'value' => ($storage['percent'] ?? 0).'%',
                ];
            })
            ->filter()
            ->values();

        return [
            'heading' => '平台概览',
            'description' => '租户活跃度、收入、套餐分布、用量异常和即将到期提醒。',
            'metrics' => [
                ['label' => '租户数', 'value' => Tenant::count()],
                ['label' => '活跃租户', 'value' => $activeTenantIds->count()],
                ['label' => '月经常收入', 'value' => self::money($mrr)],
                ['label' => '7天内到期', 'value' => $expiringSubscriptions->count()],
            ],
            'rows' => $expiringSubscriptions
                ->map(fn (TenantSubscription $subscription): array => [
                    'title' => '即将到期：'.$subscription->tenant?->name,
                    'meta' => $subscription->plan?->name ?? '-',
                    'value' => $subscription->ends_at?->format('Y-m-d') ?? '-',
                ])
                ->concat($usageRows)
                ->concat($planRows)
                ->concat(Tenant::latest()
                    ->limit(5)
                    ->get(['name', 'status', 'created_at'])
                    ->map(fn (Tenant $tenant): array => [
                        'title' => '最近租户：'.$tenant->name,
                        'meta' => self::optionLabel('tenant.status', $tenant->status),
                        'value' => $tenant->created_at?->format('Y-m-d') ?? '-',
                    ]))
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

    private static function targetOrderAmount(?int $tenantId, SalesTarget $target): float
    {
        return (float) Order::where('tenant_id', $tenantId)
            ->whereBetween('ordered_at', [$target->period_start, $target->period_end])
            ->when($target->target_type === 'user', fn ($query) => $query->where('employee_id', $target->target_id))
            ->when($target->target_type === 'department', fn ($query) => $query->whereIn('employee_id', self::departmentUserIds($tenantId, (int) $target->target_id)))
            ->sum('total_amount');
    }

    private static function targetPaymentAmount(?int $tenantId, SalesTarget $target): float
    {
        return (float) Payment::where('tenant_id', $tenantId)
            ->where('status', 'completed')
            ->whereBetween('received_at', [$target->period_start, $target->period_end])
            ->whereHas('order', function ($query) use ($tenantId, $target): void {
                $query->where('tenant_id', $tenantId)
                    ->when($target->target_type === 'user', fn ($query) => $query->where('employee_id', $target->target_id))
                    ->when($target->target_type === 'department', fn ($query) => $query->whereIn('employee_id', self::departmentUserIds($tenantId, (int) $target->target_id)));
            })
            ->sum('amount');
    }

    private static function targetNewCustomerCount(?int $tenantId, SalesTarget $target): int
    {
        return Customer::where('tenant_id', $tenantId)
            ->whereBetween('created_at', [$target->period_start, $target->period_end])
            ->when($target->target_type === 'user', fn ($query) => $query->where('owner_user_id', $target->target_id))
            ->when($target->target_type === 'department', fn ($query) => $query->whereIn('owner_user_id', self::departmentUserIds($tenantId, (int) $target->target_id)))
            ->count();
    }

    private static function departmentUserIds(?int $tenantId, int $departmentId): array
    {
        return DB::table('department_user')
            ->where('tenant_id', $tenantId)
            ->where('department_id', $departmentId)
            ->pluck('user_id')
            ->map(fn ($id): int => (int) $id)
            ->all();
    }

    private static function percent(float|int $value, float|int $total): string
    {
        return round(((float) $value) / max(1, (float) $total) * 100, 1).'%';
    }

    private static function openOpportunityValue(?int $tenantId): float
    {
        return (float) Opportunity::where('tenant_id', $tenantId)
            ->whereNull('ended_at')
            ->sum('amount');
    }
}

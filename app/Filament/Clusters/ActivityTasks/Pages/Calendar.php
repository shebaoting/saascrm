<?php

namespace App\Filament\Clusters\ActivityTasks\Pages;

use App\Filament\Clusters\ActivityTasks\ActivityTasksCluster;
use App\Models\Activity;
use App\Models\Task;
use App\Services\Crm\AuditLogService;
use App\Support\CrmMetrics;
use Carbon\CarbonImmutable;
use Carbon\CarbonPeriod;
use Filament\Facades\Filament;
use Filament\Pages\Page;
use Filament\Notifications\Notification;

class Calendar extends Page
{
    protected string $view = 'filament.clusters.activity-tasks.pages.calendar';

    protected static ?string $cluster = ActivityTasksCluster::class;

    protected static ?string $navigationLabel = '日历';

    protected static ?string $title = '日历';

    protected static ?int $navigationSort = 30;

    public string $viewMode = 'week';

    public string $cursorDate = '';

    public function mount(): void
    {
        $this->cursorDate = $this->cursorDate ?: now()->toDateString();
    }

    public function setViewMode(string $mode): void
    {
        if (in_array($mode, ['month', 'week', 'day'], true)) {
            $this->viewMode = $mode;
        }
    }

    public function previous(): void
    {
        $cursor = CarbonImmutable::parse($this->cursorDate);

        $this->cursorDate = match ($this->viewMode) {
            'month' => $cursor->subMonth()->toDateString(),
            'day' => $cursor->subDay()->toDateString(),
            default => $cursor->subWeek()->toDateString(),
        };
    }

    public function next(): void
    {
        $cursor = CarbonImmutable::parse($this->cursorDate);

        $this->cursorDate = match ($this->viewMode) {
            'month' => $cursor->addMonth()->toDateString(),
            'day' => $cursor->addDay()->toDateString(),
            default => $cursor->addWeek()->toDateString(),
        };
    }

    public function today(): void
    {
        $this->cursorDate = now()->toDateString();
    }

    public function moveTask(int $taskId, string $date): void
    {
        $tenantId = $this->tenantId();
        $task = Task::query()
            ->where('tenant_id', $tenantId)
            ->findOrFail($taskId);

        $oldDueAt = $task->due_at;
        $target = CarbonImmutable::parse($date);
        $time = $task->due_at ?: now()->setTime(9, 0);

        $task->forceFill([
            'due_at' => $target->setTime((int) $time->format('H'), (int) $time->format('i')),
        ])->save();

        app(AuditLogService::class)->record('task_rescheduled', $task, [
            'due_at' => $oldDueAt?->toDateTimeString(),
        ], [
            'due_at' => $task->due_at?->toDateTimeString(),
        ]);

        Notification::make()->success()->title('任务日期已更新')->send();
    }

    protected function getViewData(): array
    {
        $range = $this->range();
        $start = $range['start'];
        $end = $range['end'];
        $metrics = CrmMetrics::calendar();

        $tasks = Task::query()
            ->where('tenant_id', $this->tenantId())
            ->whereNotNull('due_at')
            ->whereBetween('due_at', [$start->startOfDay(), $end->endOfDay()])
            ->with(['assignee', 'customer', 'lead'])
            ->orderBy('due_at')
            ->get()
            ->groupBy(fn (Task $task): string => $task->due_at?->toDateString() ?: '');

        $activities = Activity::query()
            ->where('tenant_id', $this->tenantId())
            ->whereBetween('occurred_at', [$start->startOfDay(), $end->endOfDay()])
            ->with(['owner', 'customer', 'lead'])
            ->orderBy('occurred_at')
            ->get()
            ->groupBy(fn (Activity $activity): string => $activity->occurred_at?->toDateString() ?: '');

        return [
            ...$metrics,
            'viewMode' => $this->viewMode,
            'periodTitle' => $this->periodTitle($start, $end),
            'days' => $this->days($start, $end, $tasks, $activities),
        ];
    }

    /**
     * @return array{start: CarbonImmutable, end: CarbonImmutable}
     */
    private function range(): array
    {
        $cursor = CarbonImmutable::parse($this->cursorDate ?: now()->toDateString());

        return match ($this->viewMode) {
            'month' => [
                'start' => $cursor->startOfMonth()->startOfWeek(),
                'end' => $cursor->endOfMonth()->endOfWeek(),
            ],
            'day' => [
                'start' => $cursor,
                'end' => $cursor,
            ],
            default => [
                'start' => $cursor->startOfWeek(),
                'end' => $cursor->endOfWeek(),
            ],
        };
    }

    private function periodTitle(CarbonImmutable $start, CarbonImmutable $end): string
    {
        if ($start->isSameDay($end)) {
            return $start->format('Y-m-d');
        }

        return $start->format('Y-m-d').' 至 '.$end->format('Y-m-d');
    }

    private function days(CarbonImmutable $start, CarbonImmutable $end, $tasks, $activities): array
    {
        $days = [];

        foreach (CarbonPeriod::create($start, $end) as $date) {
            $key = $date->toDateString();

            $days[] = [
                'date' => $key,
                'label' => $date->format('m-d'),
                'weekday' => ['日', '一', '二', '三', '四', '五', '六'][$date->dayOfWeek],
                'isToday' => $date->isToday(),
                'tasks' => $tasks->get($key, collect()),
                'activities' => $activities->get($key, collect()),
            ];
        }

        return $days;
    }

    private function tenantId(): ?int
    {
        return Filament::getTenant()?->getKey();
    }
}

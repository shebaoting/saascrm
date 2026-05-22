<x-filament-panels::page>
    <div class="crm-calendar-page">
        <div class="crm-metric-heading">
            <h2 class="crm-metric-title">{{ $heading ?? '日历' }}</h2>
            <p class="crm-metric-description">{{ $periodTitle }}</p>
        </div>

        <div class="crm-calendar-toolbar">
            <div class="crm-calendar-switcher">
                @foreach (['month' => '月', 'week' => '周', 'day' => '日'] as $mode => $label)
                    <button
                        type="button"
                        wire:click="setViewMode('{{ $mode }}')"
                        class="{{ $viewMode === $mode ? 'is-active' : '' }}"
                    >
                        {{ $label }}
                    </button>
                @endforeach
            </div>

            <div class="crm-calendar-switcher">
                <button type="button" wire:click="previous">上一页</button>
                <button type="button" wire:click="today">今天</button>
                <button type="button" wire:click="next">下一页</button>
            </div>
        </div>

        <div class="crm-metric-grid">
            @foreach ($metrics ?? [] as $metric)
                <div class="crm-metric-card">
                    <div class="crm-metric-label">{{ $metric['label'] }}</div>
                    <div class="crm-metric-value">{{ $metric['value'] }}</div>
                </div>
            @endforeach
        </div>

        <div class="crm-calendar-grid {{ $viewMode === 'day' ? 'is-day' : '' }}">
            @foreach ($days as $day)
                <section
                    class="crm-calendar-day {{ $day['isToday'] ? 'is-today' : '' }}"
                    ondragover="event.preventDefault()"
                    ondrop="@this.call('moveTask', event.dataTransfer.getData('task-id'), '{{ $day['date'] }}')"
                >
                    <header>
                        <span>周{{ $day['weekday'] }}</span>
                        <strong>{{ $day['label'] }}</strong>
                    </header>

                    <div class="crm-calendar-events">
                        @foreach ($day['tasks'] as $task)
                            <article
                                class="crm-calendar-item {{ $task->due_at && $task->status !== 'completed' && $task->due_at->isPast() ? 'is-overdue' : '' }}"
                                draggable="true"
                                ondragstart="event.dataTransfer.setData('task-id', '{{ $task->id }}')"
                            >
                                <div>{{ $task->title }}</div>
                                <span>
                                    任务 / {{ $task->assignee?->name ?: '-' }}
                                    @if ($task->due_at)
                                        / {{ $task->due_at->format('H:i') }}
                                    @endif
                                </span>
                            </article>
                        @endforeach

                        @foreach ($day['activities'] as $activity)
                            <article class="crm-calendar-item is-activity">
                                <div>{{ $activity->subject ?: '未命名活动' }}</div>
                                <span>
                                    {{ \App\Support\Filament\CrmUi::valueLabel('type', $activity->type, $activity) }}
                                    / {{ $activity->owner?->name ?: '-' }}
                                    / {{ $activity->occurred_at?->format('H:i') ?: '-' }}
                                </span>
                            </article>
                        @endforeach

                        @if ($day['tasks']->isEmpty() && $day['activities']->isEmpty())
                            <div class="crm-calendar-empty">无安排</div>
                        @endif
                    </div>
                </section>
            @endforeach
        </div>
    </div>
</x-filament-panels::page>

<x-filament-panels::page>
    <div class="crm-profile-page">
        <section class="crm-profile-hero">
            <div class="crm-profile-heading">
                <div>
                    <div class="crm-profile-kicker">线索档案</div>
                    <h2 class="crm-profile-title">{{ $lead->company_name ?: $lead->contact_name ?: '未命名线索' }}</h2>
                </div>
                <div class="crm-profile-tags">
                    <span>{{ \App\Support\Filament\CrmUi::valueLabel('status', $lead->status, $lead) }}</span>
                    <span>{{ $lead->qualification_status ? \App\Support\Filament\CrmUi::valueLabel('qualification_status', $lead->qualification_status, $lead) : '未评定' }}</span>
                </div>
            </div>

            <div class="crm-profile-facts">
                <div><span>联系人</span>{{ $lead->contact_name ?: '-' }}</div>
                <div><span>负责人</span>{{ $lead->owner?->name ?: '-' }}</div>
                <div><span>电话</span>{{ $lead->phone ?: '-' }}</div>
                <div><span>邮箱</span>{{ $lead->email ?: '-' }}</div>
                <div><span>来源</span>{{ $lead->source ?: '-' }}</div>
                <div><span>最后跟进</span>{{ $lead->last_activity_at?->format('Y-m-d H:i') ?: '-' }}</div>
                <div><span>下次跟进</span>{{ $lead->next_activity_at?->format('Y-m-d H:i') ?: '-' }}</div>
                <div><span>已转客户</span>{{ $lead->convertedCustomer?->name ?: '-' }}</div>
            </div>
        </section>

        <div class="crm-metric-grid">
            @foreach ($metrics as $metric)
                <div class="crm-metric-card">
                    <div class="crm-metric-label">{{ $metric['label'] }}</div>
                    <div class="crm-metric-value">{{ $metric['value'] }}</div>
                </div>
            @endforeach
        </div>

        <div class="crm-profile-grid">
            <section class="crm-record-panel">
                <div class="crm-record-panel-title">评分解释</div>
                <div class="crm-record-list">
                    @foreach (['rules' => '规则命中', 'completeness' => '资料完整度', 'behavior' => '行为评分'] as $group => $label)
                        @forelse ($scoreBreakdown[$group] ?? [] as $item)
                            <div class="crm-record-row">
                                <div class="crm-record-name">{{ $item['name'] }}</div>
                                <div class="crm-record-meta">{{ $label }}</div>
                                <div class="crm-record-value">+{{ $item['score'] }}</div>
                            </div>
                        @empty
                            <div class="crm-record-empty">{{ $label }}暂无加分</div>
                        @endforelse
                    @endforeach
                </div>
            </section>

            <section class="crm-record-panel">
                <div class="crm-record-panel-title">活动时间线</div>
                <div class="crm-record-list">
                    @forelse ($timeline as $activity)
                        <div class="crm-record-row">
                            <div class="crm-record-name">{{ $activity->subject ?: '未命名活动' }}</div>
                            <div class="crm-record-meta">{{ \App\Support\Filament\CrmUi::valueLabel('type', $activity->type, $activity) }} / {{ $activity->owner?->name ?: '-' }}</div>
                            <div class="crm-record-value">{{ $activity->occurred_at?->format('m-d H:i') ?: '-' }}</div>
                        </div>
                    @empty
                        <div class="crm-record-empty">暂无活动</div>
                    @endforelse
                </div>
            </section>

            <section class="crm-record-panel">
                <div class="crm-record-panel-title">任务</div>
                <div class="crm-record-list">
                    @forelse ($lead->tasks->sortBy('due_at')->take(8) as $task)
                        <div class="crm-record-row">
                            <div class="crm-record-name">{{ $task->title }}</div>
                            <div class="crm-record-meta">{{ \App\Support\Filament\CrmUi::valueLabel('status', $task->status, $task) }} / {{ $task->assignee?->name ?: '-' }}</div>
                            <div class="crm-record-value">{{ $task->due_at?->format('m-d H:i') ?: '-' }}</div>
                        </div>
                    @empty
                        <div class="crm-record-empty">暂无任务</div>
                    @endforelse
                </div>
            </section>

            <section class="crm-record-panel">
                <div class="crm-record-panel-title">重复检查</div>
                <div class="crm-record-list">
                    @forelse ($duplicates as $duplicate)
                        <div class="crm-record-row">
                            <div class="crm-record-name">{{ $duplicate['name'] }}</div>
                            <div class="crm-record-meta">{{ $duplicate['type'] }}</div>
                            <div class="crm-record-value">{{ $duplicate['contact'] }}</div>
                        </div>
                    @empty
                        <div class="crm-record-empty">未发现疑似重复数据</div>
                    @endforelse
                </div>
            </section>

            <section class="crm-record-panel">
                <div class="crm-record-panel-title">附件</div>
                <div class="crm-record-list">
                    @forelse ($lead->attachments as $attachment)
                        <div class="crm-record-row">
                            <div class="crm-record-name">{{ $attachment->name ?: basename($attachment->path) }}</div>
                            <div class="crm-record-meta">{{ $attachment->mime_type ?: '-' }}</div>
                            <div class="crm-record-value">{{ $attachment->created_at?->format('Y-m-d') ?: '-' }}</div>
                        </div>
                    @empty
                        <div class="crm-record-empty">暂无附件</div>
                    @endforelse
                </div>
            </section>
        </div>
    </div>
</x-filament-panels::page>

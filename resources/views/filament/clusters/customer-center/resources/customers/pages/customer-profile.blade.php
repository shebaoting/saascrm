<x-filament-panels::page>
    <div class="crm-profile-page">
        <section class="crm-profile-hero">
            <div class="crm-profile-heading">
                <div>
                    <div class="crm-profile-kicker">客户档案</div>
                    <h2 class="crm-profile-title">{{ $customer->name }}</h2>
                </div>
                <div class="crm-profile-tags">
                    <span>{{ \App\Support\Filament\CrmUi::valueLabel('customer_type', $customer->customer_type, $customer) }}</span>
                    <span>{{ \App\Support\Filament\CrmUi::valueLabel('lifecycle_stage', $customer->lifecycle_stage, $customer) }}</span>
                </div>
            </div>

            <div class="crm-profile-facts">
                <div><span>负责人</span>{{ $customer->owner?->name ?: '-' }}</div>
                <div><span>协作人</span>{{ $customer->members->pluck('name')->join('、') ?: '-' }}</div>
                <div><span>电话</span>{{ $customer->phone ?: '-' }}</div>
                <div><span>邮箱</span>{{ $customer->email ?: '-' }}</div>
                <div><span>来源</span>{{ $customer->source ?: '-' }}</div>
                <div><span>最后跟进</span>{{ $customer->last_activity_at?->format('Y-m-d H:i') ?: '-' }}</div>
                <div><span>下次跟进</span>{{ $customer->next_activity_at?->format('Y-m-d H:i') ?: '-' }}</div>
                <div><span>最近订单</span>{{ $customer->last_order_at?->format('Y-m-d H:i') ?: '-' }}</div>
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

        @if ($customFields->isNotEmpty())
            <section class="crm-record-panel">
                <div class="crm-record-panel-title">扩展字段</div>
                <div class="crm-record-list">
                    @foreach ($customFields as $field)
                        <div class="crm-record-row">
                            <div class="crm-record-name">{{ $field['label'] }}</div>
                            <div class="crm-record-meta">{{ $field['value'] }}</div>
                            <div class="crm-record-value"></div>
                        </div>
                    @endforeach
                </div>
            </section>
        @endif

        <div class="crm-tab-bar" role="tablist">
            @foreach ($tabs as $key => $tab)
                <button
                    type="button"
                    wire:click="setTab('{{ $key }}')"
                    class="crm-tab-button {{ $activeTab === $key ? 'is-active' : '' }}"
                >
                    <span>{{ $tab['label'] }}</span>
                    <strong>{{ $tab['count'] }}</strong>
                </button>
            @endforeach
        </div>

        <section class="crm-record-panel crm-tab-panel">
            @if ($activeTab === 'timeline')
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
            @elseif ($activeTab === 'contacts')
                <div class="crm-record-panel-title">联系人</div>
                <div class="crm-record-list">
                    @forelse ($customer->contacts as $contact)
                        <div class="crm-record-row">
                            <div class="crm-record-name">{{ $contact->name }}</div>
                            <div class="crm-record-meta">{{ $contact->position ?: $contact->department ?: '-' }}</div>
                            <div class="crm-record-value">{{ $contact->phone ?: $contact->email ?: '-' }}</div>
                        </div>
                    @empty
                        <div class="crm-record-empty">暂无联系人</div>
                    @endforelse
                </div>
            @elseif ($activeTab === 'opportunities')
                <div class="crm-record-panel-title">商机</div>
                <div class="crm-record-list">
                    @forelse ($customer->opportunities->sortByDesc('updated_at') as $opportunity)
                        <div class="crm-record-row">
                            <div class="crm-record-name">{{ $opportunity->name }}</div>
                            <div class="crm-record-meta">{{ $opportunity->stage?->name ?: '-' }} / {{ \App\Support\Filament\CrmUi::valueLabel('forecast_category', $opportunity->forecast_category, $opportunity) }}</div>
                            <div class="crm-record-value">¥{{ number_format((float) $opportunity->amount, 2) }}</div>
                        </div>
                    @empty
                        <div class="crm-record-empty">暂无商机</div>
                    @endforelse
                </div>
            @elseif ($activeTab === 'quotes')
                <div class="crm-record-panel-title">报价</div>
                <div class="crm-record-list">
                    @forelse ($latestQuotes as $quote)
                        <div class="crm-record-row">
                            <div class="crm-record-name">{{ $quote->quote_number ?: $quote->title }}</div>
                            <div class="crm-record-meta">V{{ $quote->version }} / {{ \App\Support\Filament\CrmUi::valueLabel('status', $quote->status, $quote) }}</div>
                            <div class="crm-record-value">¥{{ number_format((float) $quote->total_amount, 2) }}</div>
                        </div>
                    @empty
                        <div class="crm-record-empty">暂无报价</div>
                    @endforelse
                </div>
            @elseif ($activeTab === 'orders')
                <div class="crm-record-panel-title">订单、收款与支出</div>
                <div class="crm-record-list">
                    @forelse ($latestOrders as $order)
                        <div class="crm-record-row">
                            <div class="crm-record-name">{{ $order->order_number }}</div>
                            <div class="crm-record-meta">
                                {{ \App\Support\Filament\CrmUi::valueLabel('order_status', $order->order_status, $order) }}
                                / 已收 ¥{{ number_format((float) $order->payments->where('status', 'completed')->sum('amount'), 2) }}
                                / 支出 ¥{{ number_format((float) $order->expenses->sum('amount'), 2) }}
                            </div>
                            <div class="crm-record-value">¥{{ number_format((float) $order->total_amount, 2) }}</div>
                        </div>
                    @empty
                        <div class="crm-record-empty">暂无订单</div>
                    @endforelse
                </div>
            @elseif ($activeTab === 'tasks')
                <div class="crm-record-panel-title">任务</div>
                <div class="crm-record-list">
                    @forelse ($customer->tasks->sortBy('due_at')->take(20) as $task)
                        <div class="crm-record-row">
                            <div class="crm-record-name">{{ $task->title }}</div>
                            <div class="crm-record-meta">{{ \App\Support\Filament\CrmUi::valueLabel('task.status', $task->status, $task) }} / {{ $task->assignee?->name ?: '-' }}</div>
                            <div class="crm-record-value">{{ $task->due_at?->format('m-d H:i') ?: '-' }}</div>
                        </div>
                    @empty
                        <div class="crm-record-empty">暂无任务</div>
                    @endforelse
                </div>
            @elseif ($activeTab === 'attachments')
                <div class="crm-record-panel-title">附件</div>
                <div class="crm-record-list">
                    @forelse ($customer->attachments as $attachment)
                        <div class="crm-record-row">
                            <div class="crm-record-name">{{ $attachment->name ?: basename($attachment->path) }}</div>
                            <div class="crm-record-meta">{{ \App\Support\Filament\CrmUi::valueLabel('category', $attachment->category, $attachment) }}</div>
                            <div class="crm-record-value">{{ $attachment->created_at?->format('Y-m-d') ?: '-' }}</div>
                        </div>
                    @empty
                        <div class="crm-record-empty">暂无附件</div>
                    @endforelse
                </div>
            @elseif ($activeTab === 'history')
                <div class="crm-record-panel-title">变更历史</div>
                <div class="crm-record-list">
                    @php
                        $histories = $customer->transferHistories
                            ->merge($customer->poolHistories)
                            ->merge($customer->fieldHistories)
                            ->sortByDesc('created_at')
                            ->take(20);
                    @endphp
                    @forelse ($histories as $history)
                        <div class="crm-record-row">
                            @if ($history instanceof \App\Models\FieldHistory)
                                @php
                                    $oldValue = data_get($history->old_value, 'value', '-');
                                    $newValue = data_get($history->new_value, 'value', '-');
                                    $oldText = is_scalar($oldValue) ? $oldValue : json_encode($oldValue, JSON_UNESCAPED_UNICODE);
                                    $newText = is_scalar($newValue) ? $newValue : json_encode($newValue, JSON_UNESCAPED_UNICODE);
                                @endphp
                                <div class="crm-record-name">{{ $history->field }}：{{ $oldText }} → {{ $newText }}</div>
                                <div class="crm-record-meta">字段变更</div>
                            @else
                                <div class="crm-record-name">{{ $history->reason ?: $history->action ?: '客户变更' }}</div>
                                <div class="crm-record-meta">{{ $history instanceof \App\Models\CustomerPoolHistory ? '公海记录' : '转移记录' }}</div>
                            @endif
                            <div class="crm-record-value">{{ $history->created_at?->format('m-d H:i') ?: '-' }}</div>
                        </div>
                    @empty
                        <div class="crm-record-empty">暂无历史</div>
                    @endforelse
                </div>
            @endif
        </section>
    </div>
</x-filament-panels::page>

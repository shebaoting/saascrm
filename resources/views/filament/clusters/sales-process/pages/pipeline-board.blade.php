<x-filament-panels::page>
    <div
        class="crm-board"
        x-data="{ draggingOpportunity: null, overStage: null }"
        x-on:dragend.window="draggingOpportunity = null; overStage = null"
    >
        @if (! $pipeline)
            <div class="crm-record-panel">
                <div class="crm-record-empty">暂无可用销售管道</div>
            </div>
        @else
            @foreach ($stages as $stage)
                <section
                    class="crm-board-column"
                    wire:key="pipeline-stage-{{ $stage->id }}"
                    x-bind:class="{ 'is-drop-target': overStage === {{ $stage->id }} }"
                    x-on:dragover.prevent="overStage = {{ $stage->id }}"
                    x-on:dragleave="if (overStage === {{ $stage->id }}) overStage = null"
                    x-on:drop.prevent="
                        const opportunityId = Number(event.dataTransfer.getData('text/plain'));
                        overStage = null;
                        if (opportunityId) {
                            $wire.moveToStage(opportunityId, {{ $stage->id }});
                        }
                    "
                >
                    <div class="crm-board-head">
                        <div>
                            <div class="crm-board-title">{{ $stage->name }}</div>
                            <div class="crm-board-meta">{{ $stage->probability }}% / {{ $opportunities->get($stage->id, collect())->count() }} 个</div>
                        </div>
                    </div>

                    <div class="crm-board-list">
                        @forelse ($opportunities->get($stage->id, collect()) as $opportunity)
                            @php
                                $stageIndex = $stages->search(fn ($item) => $item->id === $stage->id);
                                $previousStage = $stageIndex > 0 ? $stages->get($stageIndex - 1) : null;
                                $nextStage = $stages->get($stageIndex + 1);
                            @endphp
                            <article
                                class="crm-board-card"
                                wire:key="opportunity-card-{{ $opportunity->id }}"
                                draggable="true"
                                x-bind:class="{ 'is-dragging': draggingOpportunity === {{ $opportunity->id }} }"
                                x-on:dragstart="
                                    draggingOpportunity = {{ $opportunity->id }};
                                    event.dataTransfer.effectAllowed = 'move';
                                    event.dataTransfer.setData('text/plain', '{{ $opportunity->id }}');
                                "
                            >
                                <div class="crm-board-card-title">{{ $opportunity->name }}</div>
                                <div class="crm-board-card-meta">{{ $opportunity->customer?->name ?: '-' }}</div>
                                <div class="crm-board-card-value">¥{{ number_format((float) $opportunity->amount, 2) }}</div>
                                <div class="crm-board-actions">
                                    @if ($previousStage)
                                        <button type="button" wire:click="moveToStage({{ $opportunity->id }}, {{ $previousStage->id }})">上一阶段</button>
                                    @endif
                                    @if ($nextStage)
                                        <button type="button" wire:click="moveToStage({{ $opportunity->id }}, {{ $nextStage->id }})">下一阶段</button>
                                    @endif
                                </div>
                            </article>
                        @empty
                            <div class="crm-board-empty">暂无商机</div>
                        @endforelse
                    </div>
                </section>
            @endforeach
        @endif
    </div>
</x-filament-panels::page>

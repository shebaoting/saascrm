<x-filament-panels::page>
    <div class="crm-metric-page">
        <div class="crm-metric-heading">
            <h2 class="crm-metric-title">
                {{ $heading ?? $this->getTitle() }}
            </h2>
            @if (! empty($description))
                <p class="crm-metric-description">{{ $description }}</p>
            @endif
        </div>

        <div class="crm-metric-grid">
            @foreach ($metrics ?? [] as $metric)
                <div class="crm-metric-card">
                    <div class="crm-metric-label">{{ $metric['label'] }}</div>
                    <div class="crm-metric-value">{{ $metric['value'] }}</div>
                </div>
            @endforeach
        </div>

        <div class="crm-record-panel">
            <div class="crm-record-panel-title">
                最新记录
            </div>
            <div class="crm-record-list">
                @forelse ($rows ?? [] as $row)
                    <div class="crm-record-row">
                        <div class="crm-record-name">{{ $row['title'] }}</div>
                        <div class="crm-record-meta">{{ $row['meta'] }}</div>
                        <div class="crm-record-value">{{ $row['value'] }}</div>
                    </div>
                @empty
                    <div class="crm-record-empty">暂无数据</div>
                @endforelse
            </div>
        </div>
    </div>
</x-filament-panels::page>

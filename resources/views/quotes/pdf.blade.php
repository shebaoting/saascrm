<!doctype html>
<html lang="zh-CN">
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: "DejaVu Sans", sans-serif; color: #111827; font-size: 12px; }
        h1 { font-size: 24px; margin: 0 0 8px; }
        h2 { font-size: 16px; margin: 24px 0 8px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #d1d5db; padding: 8px; text-align: left; }
        th { background: #f3f4f6; }
        .brand { display: table; width: 100%; margin-bottom: 24px; }
        .brand-logo { display: table-cell; width: 120px; vertical-align: top; }
        .brand-logo img { max-width: 100px; max-height: 56px; }
        .brand-info { display: table-cell; vertical-align: top; }
        .muted { color: #6b7280; }
        .summary { margin-top: 16px; width: 45%; margin-left: auto; }
        .right { text-align: right; }
        .terms { white-space: pre-line; line-height: 1.7; }
    </style>
</head>
<body>
    <div class="brand">
        @if ($logoDataUri)
            <div class="brand-logo"><img src="{{ $logoDataUri }}" alt="Logo"></div>
        @endif
        <div class="brand-info">
            <h1>{{ $quote->tenant?->name }}</h1>
            <div class="muted">{{ $quote->tenant?->address ?: '-' }}</div>
            <div class="muted">{{ $quote->tenant?->contact_phone ?: '-' }} / {{ $quote->tenant?->contact_email ?: '-' }}</div>
        </div>
    </div>

    <h1>{{ $quote->title }}</h1>
    <div class="muted">报价单号：{{ $quote->quote_number }} / 版本：V{{ $quote->version }}</div>
    <div class="muted">报价公司：{{ $quote->tenant?->name }} / 客户：{{ $quote->customer?->name }}</div>
    <div class="muted">联系人：{{ $quote->contact?->name ?: '-' }} / 有效期：{{ $quote->valid_until?->format('Y-m-d') ?: '-' }}</div>

    <h2>报价明细</h2>
    <table>
        <thead>
            <tr>
                <th>商品</th>
                <th>SKU</th>
                <th class="right">数量</th>
                <th class="right">单价</th>
                <th class="right">折扣</th>
                <th class="right">小计</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($quote->items as $item)
                <tr>
                    <td>{{ $item->product_name }}</td>
                    <td>{{ $item->sku_code }}</td>
                    <td class="right">{{ $item->quantity }}</td>
                    <td class="right">{{ number_format((float) $item->unit_price, 2) }}</td>
                    <td class="right">{{ number_format((float) $item->discount_amount, 2) }}</td>
                    <td class="right">{{ number_format((float) $item->subtotal_amount, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table class="summary">
        <tr><th>商品小计</th><td class="right">{{ number_format((float) $quote->subtotal_amount, 2) }}</td></tr>
        <tr><th>整单折扣</th><td class="right">{{ number_format((float) $quote->discount_amount, 2) }}</td></tr>
        <tr><th>税金</th><td class="right">{{ number_format((float) $quote->total_tax, 2) }}</td></tr>
        <tr><th>报价总额</th><td class="right">{{ number_format((float) $quote->total_amount, 2) }}</td></tr>
    </table>

    @if ($quote->notes)
        <h2>备注条款</h2>
        <div>{{ $quote->notes }}</div>
    @endif

    @if ($terms)
        <h2>默认条款</h2>
        <div class="terms">{{ $terms }}</div>
    @endif
</body>
</html>

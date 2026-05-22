<?php

namespace App\Services\Crm;

use App\Models\Quote;
use App\Models\QuoteItem;

class QuoteCalculatorService
{
    public function snapshotItem(QuoteItem $item): QuoteItem
    {
        $sku = $item->sku()->with('product')->first();

        if ($sku) {
            $item->forceFill([
                'product_id' => $item->product_id ?: $sku->product_id,
                'product_name' => $item->product_name ?: $sku->product?->name,
                'sku_code' => $item->sku_code ?: $sku->sku_code,
                'specifications' => $item->specifications ?: $sku->specifications,
                'list_price' => $item->list_price ?: $sku->price,
                'unit_price' => $item->unit_price ?: $sku->price,
                'cost_price' => $item->cost_price ?: $sku->cost_price,
                'tax_rate' => $item->tax_rate ?: $sku->product?->tax_rate,
            ]);
        }

        $quantity = max(1, (int) $item->quantity);
        $subtotal = ($quantity * (float) $item->unit_price) - (float) $item->discount_amount;

        $item->forceFill([
            'quantity' => $quantity,
            'subtotal_amount' => max(0, $subtotal),
        ]);

        return $item;
    }

    public function recalculate(Quote $quote): Quote
    {
        $items = $quote->items()->get();
        $subtotal = $items->sum('subtotal_amount');
        $totalCost = $items->sum(fn (QuoteItem $item): float => (float) $item->cost_price * (int) $item->quantity);
        $totalTax = $items->sum(fn (QuoteItem $item): float => (float) $item->subtotal_amount * ((float) $item->tax_rate / 100));
        $total = max(0, $subtotal - (float) $quote->discount_amount);
        $profit = $total - $totalCost;

        $quote->forceFill([
            'subtotal_amount' => $subtotal,
            'total_amount' => $total,
            'total_cost' => $totalCost,
            'total_profit' => $profit,
            'total_tax' => $totalTax,
            'profit_margin' => $total > 0 ? round($profit / $total * 100, 2) : 0,
        ])->saveQuietly();

        return $quote->refresh();
    }
}

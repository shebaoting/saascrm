<?php

namespace App\Services\Crm;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderPaymentPlan;

class OrderFinanceService
{
    public function snapshotItem(OrderItem $item): OrderItem
    {
        $sku = $item->sku()->with('product')->first();
        $product = $item->product()->first();

        if ($sku) {
            $item->forceFill([
                'product_id' => $item->product_id ?: $sku->product_id,
                'product_name' => $item->product_name ?: $sku->product?->name,
                'sku_code' => $item->sku_code ?: $sku->sku_code,
                'specifications' => $item->specifications ?: $sku->specifications,
                'unit_price' => $item->unit_price ?: $sku->price,
                'cost_price' => $item->cost_price ?: $sku->cost_price,
                'tax_rate' => $item->tax_rate ?: $sku->product?->tax_rate,
            ]);
        } elseif ($product) {
            $item->forceFill([
                'product_name' => $item->product_name ?: $product->name,
                'tax_rate' => $item->tax_rate ?: $product->tax_rate,
            ]);
        }

        $quantity = max(1, (int) $item->quantity);

        $item->forceFill([
            'quantity' => $quantity,
            'subtotal_amount' => max(0, $quantity * (float) $item->unit_price),
        ]);

        return $item;
    }

    public function refresh(Order $order): Order
    {
        $items = $order->items()->get();
        $subtotal = $items->isNotEmpty() ? $items->sum('subtotal_amount') : (float) $order->subtotal_amount;
        $totalCost = $items->isNotEmpty() ? $items->sum(fn (OrderItem $item): float => (float) $item->cost_price * (int) $item->quantity) : (float) $order->total_cost;
        $total = $items->isNotEmpty() ? max(0, $subtotal - (float) $order->discount_amount) : (float) $order->total_amount;

        $paid = $order->payments()
            ->where('status', 'completed')
            ->sum('amount');

        $expenses = $order->expenses()
            ->whereIn('status', ['approved', 'pending'])
            ->sum('amount');

        $status = match (true) {
            $paid <= 0 => 'unpaid',
            $paid < (float) $order->total_amount => 'partial_paid',
            default => 'paid',
        };

        $order->forceFill([
            'subtotal_amount' => $subtotal,
            'total_amount' => $total,
            'total_cost' => $totalCost,
            'payment_status' => $status,
            'gross_profit' => $total - $totalCost - (float) $expenses,
        ])->saveQuietly();

        return $order->refresh();
    }

    public function refreshPaymentPlan(?OrderPaymentPlan $plan): ?OrderPaymentPlan
    {
        if (! $plan) {
            return null;
        }

        $received = $plan->payments()
            ->where('status', 'completed')
            ->sum('amount');

        $status = match (true) {
            $received >= (float) $plan->plan_amount && (float) $plan->plan_amount > 0 => 'paid',
            $received > 0 => 'partial',
            $plan->plan_date?->lt(today()) => 'overdue',
            default => 'pending',
        };

        $plan->forceFill([
            'received_amount' => $received,
            'status' => $status,
        ])->saveQuietly();

        return $plan->refresh();
    }
}

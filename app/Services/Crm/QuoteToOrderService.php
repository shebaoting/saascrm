<?php

namespace App\Services\Crm;

use App\Models\Order;
use App\Models\Quote;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class QuoteToOrderService
{
    public function convert(Quote $quote): Order
    {
        return DB::transaction(function () use ($quote): Order {
            app(QuoteCalculatorService::class)->recalculate($quote);
            $quote->refresh()->load('items');

            $order = Order::create([
                'tenant_id' => $quote->tenant_id,
                'order_number' => $this->nextOrderNumber($quote->tenant_id),
                'customer_id' => $quote->customer_id,
                'contact_id' => $quote->contact_id,
                'opportunity_id' => $quote->opportunity_id,
                'quote_id' => $quote->id,
                'employee_id' => $quote->user_id,
                'subtotal_amount' => $quote->subtotal_amount,
                'discount_amount' => $quote->discount_amount,
                'total_amount' => $quote->total_amount,
                'total_cost' => $quote->total_cost,
                'gross_profit' => $quote->total_profit,
                'order_source' => 'quote',
                'order_status' => 'confirmed',
                'payment_status' => 'unpaid',
                'ordered_at' => now(),
                'notes' => $quote->notes,
                'created_by' => Auth::id(),
            ]);

            foreach ($quote->items as $item) {
                $order->items()->create([
                    'tenant_id' => $quote->tenant_id,
                    'product_id' => $item->product_id,
                    'product_sku_id' => $item->product_sku_id,
                    'product_name' => $item->product_name,
                    'sku_code' => $item->sku_code,
                    'specifications' => $item->specifications,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unit_price,
                    'cost_price' => $item->cost_price,
                    'tax_rate' => $item->tax_rate,
                    'subtotal_amount' => $item->subtotal_amount,
                ]);
            }

            $quote->forceFill([
                'status' => 'accepted',
                'accepted_at' => now(),
            ])->save();

            $quote->customer()->update([
                'lifecycle_stage' => 'won',
                'first_order_at' => $quote->customer?->first_order_at ?: now(),
                'last_order_at' => now(),
            ]);

            return $order;
        });
    }

    private function nextOrderNumber(int $tenantId): string
    {
        $prefix = 'SO'.now()->format('Ymd');
        $count = Order::query()
            ->where('tenant_id', $tenantId)
            ->where('order_number', 'like', $prefix.'%')
            ->count() + 1;

        return $prefix.str_pad((string) $count, 4, '0', STR_PAD_LEFT);
    }
}

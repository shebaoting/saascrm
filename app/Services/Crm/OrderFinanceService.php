<?php

namespace App\Services\Crm;

use App\Models\Order;

class OrderFinanceService
{
    public function refresh(Order $order): Order
    {
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
            'payment_status' => $status,
            'gross_profit' => (float) $order->total_amount - (float) $order->total_cost - (float) $expenses,
        ])->saveQuietly();

        return $order->refresh();
    }
}

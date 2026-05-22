<?php

namespace App\Services\Crm;

use App\Models\Order;
use App\Models\Notification as CrmNotification;
use App\Models\Quote;
use App\Models\QuoteApprovalRequest;
use App\Models\Setting;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class QuoteToOrderService
{
    public function convert(Quote $quote): Order
    {
        app(QuoteCalculatorService::class)->recalculate($quote);
        $quote->refresh();

        $this->ensureApprovalSatisfied($quote);

        return DB::transaction(function () use ($quote): Order {
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

            $previousQuoteStatus = $quote->status;

            $quote->forceFill([
                'status' => 'accepted',
                'accepted_at' => now(),
            ])->save();

            if ($quote->opportunity_id) {
                Quote::query()
                    ->where('tenant_id', $quote->tenant_id)
                    ->where('opportunity_id', $quote->opportunity_id)
                    ->whereKeyNot($quote->id)
                    ->where('status', '!=', 'accepted')
                    ->update([
                        'status' => 'expired',
                        'superseded_at' => now(),
                    ]);
            }

            $quote->customer()->update([
                'lifecycle_stage' => 'won',
                'first_order_at' => $quote->customer?->first_order_at ?: now(),
                'last_order_at' => now(),
            ]);

            app(ActivityService::class)->recordSystemEvent(
                $quote->tenant_id,
                '报价转订单：'.$order->order_number,
                '报价 '.$quote->quote_number.' 已转为订单，金额 '.number_format((float) $order->total_amount, 2),
                [
                    'customer' => $quote->customer,
                    'contact' => $quote->contact,
                    'opportunity' => $quote->opportunity,
                ],
            );

            app(AuditLogService::class)->record('quote_converted_to_order', $quote, [
                'status' => $previousQuoteStatus,
            ], [
                'status' => 'accepted',
                'order_id' => $order->id,
                'order_number' => $order->order_number,
            ]);

            return $order;
        });
    }

    private function ensureApprovalSatisfied(Quote $quote): void
    {
        if (in_array($quote->status, ['pending_approval', 'rejected', 'expired'], true)) {
            throw ValidationException::withMessages([
                'quote_id' => '当前报价状态为「'.$quote->status.'」，不能直接转订单。',
            ]);
        }

        if ($quote->opportunity_id && Quote::query()
            ->where('tenant_id', $quote->tenant_id)
            ->where('opportunity_id', $quote->opportunity_id)
            ->where('status', 'accepted')
            ->whereKeyNot($quote->id)
            ->exists()) {
            throw ValidationException::withMessages([
                'quote_id' => '该商机已有已接受报价，其他版本不能再转订单。',
            ]);
        }

        $reasons = $this->approvalReasons($quote);

        if ($reasons === [] || $quote->status === 'approved') {
            return;
        }

        $approval = $quote->approvals()
            ->where('status', 'pending')
            ->latest('requested_at')
            ->first();

        if (! $approval) {
            $approval = QuoteApprovalRequest::create([
                'tenant_id' => $quote->tenant_id,
                'quote_id' => $quote->id,
                'requested_by' => Auth::id() ?: $quote->user_id,
                'approver_id' => $this->approvalRules($quote->tenant_id)['approver_id'] ?? null,
                'status' => 'pending',
                'reason' => implode('；', $reasons),
                'requested_at' => now(),
            ]);

            $this->notifyApprover($quote, $approval);
        }

        $quote->forceFill(['status' => 'pending_approval'])->save();

        throw ValidationException::withMessages([
            'quote_id' => '该报价触发审批规则：'.implode('；', $reasons).'。审批通过后才能转订单。',
        ]);
    }

    private function approvalReasons(Quote $quote): array
    {
        $rules = $this->approvalRules($quote->tenant_id);
        $reasons = [];

        if (array_key_exists('min_profit_margin', $rules) && $rules['min_profit_margin'] !== null && $quote->profit_margin < (float) $rules['min_profit_margin']) {
            $reasons[] = '毛利率 '.$quote->profit_margin.'% 低于 '.(float) $rules['min_profit_margin'].'%';
        }

        $subtotal = (float) $quote->subtotal_amount;
        $discountRate = $subtotal > 0 ? round((float) $quote->discount_amount / $subtotal * 100, 2) : 0;

        if (array_key_exists('max_discount_rate', $rules) && $rules['max_discount_rate'] !== null && $discountRate > (float) $rules['max_discount_rate']) {
            $reasons[] = '折扣率 '.$discountRate.'% 高于 '.(float) $rules['max_discount_rate'].'%';
        }

        if (array_key_exists('max_amount_without_approval', $rules) && $rules['max_amount_without_approval'] !== null && $quote->total_amount > (float) $rules['max_amount_without_approval']) {
            $reasons[] = '报价金额超过免审上限 '.number_format((float) $rules['max_amount_without_approval'], 2);
        }

        return $reasons;
    }

    private function approvalRules(int $tenantId): array
    {
        $setting = Setting::query()
            ->where('tenant_id', $tenantId)
            ->where('key', 'quote_approval_rules')
            ->first();

        return is_array($setting?->value) ? $setting->value : [];
    }

    private function notifyApprover(Quote $quote, QuoteApprovalRequest $approval): void
    {
        if (! $approval->approver_id) {
            return;
        }

        CrmNotification::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $quote->tenant_id,
            'type' => 'quote_approval_requested',
            'notifiable_type' => \App\Models\User::class,
            'notifiable_id' => $approval->approver_id,
            'data' => json_encode([
                'title' => '新的报价审批',
                'body' => $quote->quote_number.' 需要审批',
                'record_type' => Quote::class,
                'record_id' => $quote->id,
            ], JSON_UNESCAPED_UNICODE),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
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

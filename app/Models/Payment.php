<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Payment extends TenantModel
{
    use SoftDeletes;

    protected $casts = [
        'plan_date' => 'date',
        'received_at' => 'datetime',
        'amount' => 'decimal:2',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function paymentPlan(): BelongsTo
    {
        return $this->belongsTo(OrderPaymentPlan::class, 'payment_plan_id');
    }
}

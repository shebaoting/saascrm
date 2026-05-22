<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OrderPaymentPlan extends TenantModel
{
    protected $casts = [
        'plan_date' => 'date',
        'plan_amount' => 'decimal:2',
        'received_amount' => 'decimal:2',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'payment_plan_id');
    }
}

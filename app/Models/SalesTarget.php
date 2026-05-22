<?php

namespace App\Models;

class SalesTarget extends TenantModel
{
    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
        'target_amount' => 'decimal:2',
        'target_payment_amount' => 'decimal:2',
    ];
}

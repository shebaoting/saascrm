<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;

class Plan extends CrmModel
{
    protected $casts = ['features' => 'array', 'is_active' => 'boolean', 'price_monthly' => 'decimal:2', 'price_yearly' => 'decimal:2'];

    public function subscriptions(): HasMany
    {
        return $this->hasMany(TenantSubscription::class);
    }
}

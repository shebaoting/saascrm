<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;

class Plan extends CrmModel
{
    protected $casts = [
        'features' => 'array',
        'is_active' => 'boolean',
        'price_monthly' => 'decimal:2',
        'price_yearly' => 'decimal:2',
        'max_users' => 'integer',
        'max_leads' => 'integer',
        'max_customers' => 'integer',
        'max_storage_mb' => 'integer',
        'max_custom_fields' => 'integer',
        'max_automation_rules' => 'integer',
        'max_imports_daily' => 'integer',
        'max_exports_daily' => 'integer',
    ];

    public function subscriptions(): HasMany
    {
        return $this->hasMany(TenantSubscription::class);
    }
}

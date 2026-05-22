<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;

class AutomationRule extends TenantModel
{
    protected $casts = [
        'conditions' => 'array',
        'is_active' => 'boolean',
        'last_run_at' => 'datetime',
    ];

    public function actions(): HasMany
    {
        return $this->hasMany(AutomationAction::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AutomationAction extends TenantModel
{
    protected $casts = [
        'payload' => 'array',
    ];

    public function rule(): BelongsTo
    {
        return $this->belongsTo(AutomationRule::class, 'automation_rule_id');
    }
}

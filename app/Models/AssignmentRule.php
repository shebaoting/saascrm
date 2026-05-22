<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AssignmentRule extends TenantModel
{
    protected $casts = [
        'user_ids' => 'array',
        'is_active' => 'boolean',
    ];

    public function conditions(): HasMany
    {
        return $this->hasMany(AssignmentRuleCondition::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }
}

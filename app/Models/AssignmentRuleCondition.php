<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssignmentRuleCondition extends TenantModel
{
    protected $casts = [
        'value' => 'array',
    ];

    public function rule(): BelongsTo
    {
        return $this->belongsTo(AssignmentRule::class, 'assignment_rule_id');
    }
}

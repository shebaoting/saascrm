<?php

namespace App\Models;

class LeadScoreRule extends TenantModel
{
    protected $casts = [
        'value' => 'array',
        'score' => 'integer',
        'is_active' => 'boolean',
    ];
}

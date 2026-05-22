<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MergeHistory extends TenantModel
{
    const UPDATED_AT = null;

    protected $casts = [
        'merged_fields' => 'array',
        'merged_relations' => 'array',
        'created_at' => 'datetime',
    ];

    public function mergedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'merged_by');
    }
}

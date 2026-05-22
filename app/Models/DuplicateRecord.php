<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DuplicateRecord extends TenantModel
{
    protected $casts = [
        'payload' => 'array',
        'resolved_at' => 'datetime',
    ];

    public function resolver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }
}

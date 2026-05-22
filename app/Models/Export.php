<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Export extends TenantModel
{
    protected $casts = [
        'completed_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

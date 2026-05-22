<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FailedImportRow extends TenantModel
{
    protected $casts = [
        'data' => 'array',
    ];

    public function import(): BelongsTo
    {
        return $this->belongsTo(Import::class);
    }
}

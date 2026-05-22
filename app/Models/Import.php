<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Import extends TenantModel
{
    protected $casts = [
        'completed_at' => 'datetime',
    ];

    public function failedRows(): HasMany
    {
        return $this->hasMany(FailedImportRow::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

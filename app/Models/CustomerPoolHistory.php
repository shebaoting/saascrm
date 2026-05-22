<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerPoolHistory extends TenantModel
{
    const UPDATED_AT = null;

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function fromUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'from_user_id');
    }

    public function toUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'to_user_id');
    }

    public function operatorUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'operated_by');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notification extends CrmModel
{
    public $incrementing = false;

    protected $keyType = 'string';

    protected $casts = ['read_at' => 'datetime'];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function notifiable(): BelongsTo
    {
        return $this->belongsTo(User::class, 'notifiable_id')
            ->where('notifiable_type', User::class);
    }
}

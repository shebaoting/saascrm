<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OpportunityStageHistory extends TenantModel
{
    public $timestamps = false;

    protected $casts = [
        'changed_at' => 'datetime',
    ];

    public function opportunity(): BelongsTo
    {
        return $this->belongsTo(Opportunity::class);
    }
}

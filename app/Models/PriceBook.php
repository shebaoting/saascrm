<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;

class PriceBook extends TenantModel
{
    protected $casts = [
        'is_default' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(PriceBookItem::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends TenantModel
{
    use SoftDeletes;

    protected $casts = [
        'tax_rate' => 'decimal:2',
        'is_on_sale' => 'boolean',
    ];

    public function group(): BelongsTo
    {
        return $this->belongsTo(ProductGroup::class, 'group_id');
    }

    public function skus(): HasMany
    {
        return $this->hasMany(ProductSku::class);
    }
}

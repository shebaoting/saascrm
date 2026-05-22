<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PriceBookItem extends TenantModel
{
    protected $casts = [
        'price' => 'decimal:2',
        'min_price' => 'decimal:2',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
    ];

    public function priceBook(): BelongsTo
    {
        return $this->belongsTo(PriceBook::class);
    }

    public function sku(): BelongsTo
    {
        return $this->belongsTo(ProductSku::class, 'product_sku_id');
    }
}

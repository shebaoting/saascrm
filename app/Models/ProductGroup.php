<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductGroup extends TenantModel
{
    use SoftDeletes;

    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'group_id');
    }
}

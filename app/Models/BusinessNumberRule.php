<?php

namespace App\Models;

class BusinessNumberRule extends TenantModel
{
    protected $casts = [
        'is_active' => 'boolean',
    ];
}

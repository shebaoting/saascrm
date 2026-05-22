<?php

namespace App\Models;

class CustomerPoolRule extends TenantModel
{
    protected $casts = [
        'department_ids' => 'array',
        'is_active' => 'boolean',
    ];
}

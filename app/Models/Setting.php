<?php

namespace App\Models;

class Setting extends TenantModel
{
    protected $casts = [
        'value' => 'array',
    ];
}

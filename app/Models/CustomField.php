<?php

namespace App\Models;

class CustomField extends TenantModel
{
    protected $casts = [
        'is_visible' => 'boolean',
        'is_required' => 'boolean',
        'is_filterable' => 'boolean',
        'is_list_visible' => 'boolean',
        'is_show_in_tracking' => 'boolean',
        'data' => 'array',
    ];
}

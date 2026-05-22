<?php

namespace App\Models;

class FieldHistory extends CrmModel
{
    public $timestamps = false;

    protected $casts = [
        'old_value' => 'array',
        'new_value' => 'array',
        'created_at' => 'datetime',
    ];
}

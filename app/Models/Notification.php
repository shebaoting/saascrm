<?php

namespace App\Models;

class Notification extends CrmModel
{
    public $incrementing = false;

    protected $keyType = 'string';

    protected $casts = ['read_at' => 'datetime'];
}

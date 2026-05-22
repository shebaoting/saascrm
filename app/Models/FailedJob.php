<?php

namespace App\Models;

class FailedJob extends CrmModel
{
    protected $table = 'failed_jobs';

    public $timestamps = false;

    protected $casts = [
        'failed_at' => 'datetime',
    ];
}

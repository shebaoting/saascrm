<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;

abstract class TenantModel extends CrmModel
{
    use BelongsToTenant;
}

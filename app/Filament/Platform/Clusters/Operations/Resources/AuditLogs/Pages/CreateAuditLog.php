<?php

namespace App\Filament\Platform\Clusters\Operations\Resources\AuditLogs\Pages;

use App\Filament\Platform\Clusters\Operations\Resources\AuditLogs\AuditLogResource;
use Filament\Resources\Pages\CreateRecord;

class CreateAuditLog extends CreateRecord
{
    protected static string $resource = AuditLogResource::class;
}

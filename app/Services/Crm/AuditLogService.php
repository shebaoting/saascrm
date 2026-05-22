<?php

namespace App\Services\Crm;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class AuditLogService
{
    public function record(string $action, Model $record, ?array $oldValues = null, ?array $newValues = null): AuditLog
    {
        return AuditLog::create([
            'tenant_id' => $record->getAttribute('tenant_id'),
            'user_id' => Auth::id(),
            'action' => $action,
            'model_type' => $record::class,
            'model_id' => $record->getKey(),
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => request()?->ip(),
            'user_agent' => request()?->userAgent(),
            'created_at' => now(),
        ]);
    }
}

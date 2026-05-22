<?php

namespace App\Services\Crm;

use App\Models\FieldHistory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class FieldHistoryService
{
    /**
     * @param  array<string>|null  $fields
     */
    public function record(Model $record, ?array $fields = null): void
    {
        $tenantId = $record->getAttribute('tenant_id');
        $changes = $record->getChanges();
        $ignored = ['updated_at', 'created_at', 'deleted_at'];

        foreach ($changes as $field => $newValue) {
            if (in_array($field, $ignored, true)) {
                continue;
            }

            if ($fields !== null && ! in_array($field, $fields, true)) {
                continue;
            }

            FieldHistory::create([
                'tenant_id' => $tenantId,
                'user_id' => Auth::id(),
                'model_type' => $record::class,
                'model_id' => $record->getKey(),
                'field' => $field,
                'old_value' => $this->wrap($record->getOriginal($field)),
                'new_value' => $this->wrap($newValue),
                'ip_address' => request()?->ip(),
                'created_at' => now(),
            ]);
        }
    }

    /**
     * @return array{value: mixed}|null
     */
    private function wrap(mixed $value): ?array
    {
        if ($value === null) {
            return null;
        }

        return ['value' => $value];
    }
}

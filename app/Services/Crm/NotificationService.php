<?php

namespace App\Services\Crm;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class NotificationService
{
    public function send(int $tenantId, int $userId, string $type, string $title, ?string $body = null, ?Model $record = null): Notification
    {
        return Notification::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $tenantId,
            'type' => $type,
            'notifiable_type' => User::class,
            'notifiable_id' => $userId,
            'data' => json_encode([
                'title' => $title,
                'body' => $body,
                'record_type' => $record ? $record::class : null,
                'record_id' => $record?->getKey(),
            ], JSON_UNESCAPED_UNICODE),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function markRead(Notification $notification): Notification
    {
        $notification->forceFill(['read_at' => now()])->save();

        return $notification->refresh();
    }

    public function markAllRead(int $tenantId, int $userId): int
    {
        return Notification::query()
            ->where('tenant_id', $tenantId)
            ->where('notifiable_type', User::class)
            ->where('notifiable_id', $userId)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }

    /**
     * @return array{title: string, body: string|null, record_type: string|null, record_id: int|null}
     */
    public function payload(Notification $notification): array
    {
        $data = is_array($notification->data)
            ? $notification->data
            : json_decode((string) $notification->data, true);

        $data = is_array($data) ? $data : [];

        return [
            'title' => (string) ($data['title'] ?? $notification->type),
            'body' => $data['body'] ?? null,
            'record_type' => $data['record_type'] ?? null,
            'record_id' => isset($data['record_id']) ? (int) $data['record_id'] : null,
        ];
    }
}

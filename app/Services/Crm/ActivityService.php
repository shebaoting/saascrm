<?php

namespace App\Services\Crm;

use App\Models\Activity;
use App\Models\Task;
use Illuminate\Support\Facades\DB;

class ActivityService
{
    public function syncTimeline(Activity $activity): void
    {
        DB::transaction(function () use ($activity): void {
            $updates = [
                'last_activity_at' => $activity->occurred_at,
                'next_activity_at' => $activity->next_follow_at,
            ];

            if ($activity->lead_id) {
                $activity->lead()->withoutGlobalScopes()->update($updates);
            }

            if ($activity->customer_id) {
                $activity->customer()->withoutGlobalScopes()->update($updates);
            }

            if ($activity->next_follow_at) {
                Task::query()->firstOrCreate([
                    'tenant_id' => $activity->tenant_id,
                    'lead_id' => $activity->lead_id,
                    'customer_id' => $activity->customer_id,
                    'contact_id' => $activity->contact_id,
                    'opportunity_id' => $activity->opportunity_id,
                    'creator_id' => $activity->created_by ?: $activity->owner_user_id,
                    'assignee_id' => $activity->owner_user_id,
                    'due_at' => $activity->next_follow_at,
                    'title' => $activity->subject ? '跟进：'.$activity->subject : '下次跟进',
                ], [
                    'description' => $activity->outcome ?: $activity->content,
                    'start_at' => $activity->next_follow_at,
                    'status' => 'not_started',
                    'priority' => 'normal',
                ]);
            }
        });
    }
}

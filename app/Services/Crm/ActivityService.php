<?php

namespace App\Services\Crm;

use App\Models\Activity;
use App\Models\Contact;
use App\Models\Customer;
use App\Models\Lead;
use App\Models\Opportunity;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
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

            if ($activity->next_follow_at && $activity->type !== 'system') {
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

    /**
     * @param  array<string, Model|int|null>  $relations
     */
    public function recordSystemEvent(int $tenantId, string $subject, ?string $content = null, array $relations = []): Activity
    {
        $payload = $this->relationPayload($relations);
        $ownerUserId = Auth::id()
            ?: $payload['owner_user_id']
            ?: User::query()
                ->whereHas('tenants', fn ($query) => $query->whereKey($tenantId))
                ->value('id');

        return Activity::create([
            'tenant_id' => $tenantId,
            'lead_id' => $payload['lead_id'],
            'customer_id' => $payload['customer_id'],
            'contact_id' => $payload['contact_id'],
            'opportunity_id' => $payload['opportunity_id'],
            'type' => 'system',
            'direction' => 'outgoing',
            'subject' => $subject,
            'content' => $content,
            'occurred_at' => now(),
            'owner_user_id' => $ownerUserId,
            'created_by' => Auth::id(),
        ]);
    }

    /**
     * @param  array<string, Model|int|null>  $relations
     * @return array{lead_id: ?int, customer_id: ?int, contact_id: ?int, opportunity_id: ?int, owner_user_id: ?int}
     */
    private function relationPayload(array $relations): array
    {
        $lead = $relations['lead'] ?? null;
        $customer = $relations['customer'] ?? null;
        $contact = $relations['contact'] ?? null;
        $opportunity = $relations['opportunity'] ?? null;

        if ($opportunity instanceof Opportunity) {
            $customer ??= $opportunity->customer;
            $contact ??= $opportunity->contact;
        }

        if ($contact instanceof Contact) {
            $customer ??= $contact->customer;
        }

        return [
            'lead_id' => $lead instanceof Lead ? $lead->id : ($relations['lead_id'] ?? null),
            'customer_id' => $customer instanceof Customer ? $customer->id : ($relations['customer_id'] ?? null),
            'contact_id' => $contact instanceof Contact ? $contact->id : ($relations['contact_id'] ?? null),
            'opportunity_id' => $opportunity instanceof Opportunity ? $opportunity->id : ($relations['opportunity_id'] ?? null),
            'owner_user_id' => $this->ownerUserId($lead, $customer, $opportunity, $relations),
        ];
    }

    /**
     * @param  array<string, Model|int|null>  $relations
     */
    private function ownerUserId(mixed $lead, mixed $customer, mixed $opportunity, array $relations): ?int
    {
        if ($lead instanceof Lead) {
            return $lead->owner_user_id;
        }

        if ($opportunity instanceof Opportunity) {
            return $opportunity->responsible_user_id;
        }

        if ($customer instanceof Customer) {
            return $customer->owner_user_id;
        }

        return $relations['owner_user_id'] ?? null;
    }
}

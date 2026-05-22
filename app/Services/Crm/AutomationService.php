<?php

namespace App\Services\Crm;

use App\Models\Activity;
use App\Models\AutomationAction;
use App\Models\AutomationRule;
use App\Models\Customer;
use App\Models\Lead;
use App\Models\Notification;
use App\Models\Opportunity;
use App\Models\Order;
use App\Models\Quote;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class AutomationService
{
    public function run(string $triggerType, Model $record): void
    {
        $tenantId = (int) $record->getAttribute('tenant_id');

        if (! $tenantId) {
            return;
        }

        AutomationRule::query()
            ->with('actions')
            ->where('tenant_id', $tenantId)
            ->where('trigger_type', $triggerType)
            ->where('target_type', $this->targetType($record))
            ->where('is_active', true)
            ->get()
            ->each(function (AutomationRule $rule) use ($record): void {
                if (! $this->matches($rule, $record)) {
                    return;
                }

                $rule->actions
                    ->sortBy('sort_order')
                    ->each(fn (AutomationAction $action) => $this->execute($action, $record));

                $rule->forceFill(['last_run_at' => now()])->save();
            });
    }

    private function matches(AutomationRule $rule, Model $record): bool
    {
        $conditions = Arr::wrap($rule->conditions);

        if ($conditions === []) {
            return true;
        }

        foreach ($conditions as $key => $condition) {
            if (is_array($condition)) {
                $field = $condition['field'] ?? null;
                $operator = $condition['operator'] ?? '=';
                $value = $condition['value'] ?? null;
            } else {
                $field = is_string($key) ? $key : null;
                $operator = '=';
                $value = $condition;
            }

            if (! $field) {
                continue;
            }

            $actual = data_get($record, $field);

            $passed = match ($operator) {
                '!=', '<>' => (string) $actual !== (string) $value,
                'contains' => str_contains((string) $actual, (string) $value),
                'filled' => filled($actual),
                'blank' => blank($actual),
                default => (string) $actual === (string) $value,
            };

            if (! $passed) {
                return false;
            }
        }

        return true;
    }

    private function execute(AutomationAction $action, Model $record): void
    {
        $payload = Arr::wrap($action->payload);

        match ($action->action_type) {
            'create_task' => $this->createTask($action, $record, $payload),
            'send_notification' => $this->notify($action, $record, $payload),
            'assign_owner' => $this->assignOwner($record, $payload),
            'move_to_pool' => $this->moveToPool($record, $payload),
            default => null,
        };
    }

    private function createTask(AutomationAction $action, Model $record, array $payload): void
    {
        $ownerId = (int) ($payload['assignee_id'] ?? $this->ownerId($record) ?? Auth::id());

        if (! $ownerId) {
            return;
        }

        Task::create([
            'tenant_id' => $action->tenant_id,
            'lead_id' => $record instanceof Lead ? $record->id : null,
            'customer_id' => $record instanceof Customer ? $record->id : ($record->customer_id ?? null),
            'contact_id' => $record->contact_id ?? null,
            'opportunity_id' => $record instanceof Opportunity ? $record->id : ($record->opportunity_id ?? null),
            'title' => $payload['title'] ?? '自动化跟进任务',
            'description' => $payload['description'] ?? null,
            'status' => 'not_started',
            'priority' => $payload['priority'] ?? 'medium',
            'due_at' => now()->addDays((int) ($payload['due_days'] ?? 1)),
            'creator_id' => Auth::id() ?: $ownerId,
            'assignee_id' => $ownerId,
        ]);
    }

    private function notify(AutomationAction $action, Model $record, array $payload): void
    {
        $userId = (int) ($payload['user_id'] ?? $this->ownerId($record) ?? Auth::id());

        if (! $userId) {
            return;
        }

        Notification::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $action->tenant_id,
            'type' => 'crm_automation',
            'notifiable_type' => User::class,
            'notifiable_id' => $userId,
            'data' => json_encode([
                'title' => $payload['title'] ?? 'CRM 自动化提醒',
                'body' => $payload['body'] ?? class_basename($record).' 触发了自动化规则',
                'record_type' => $record::class,
                'record_id' => $record->getKey(),
            ], JSON_UNESCAPED_UNICODE),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function assignOwner(Model $record, array $payload): void
    {
        $userId = $payload['user_id'] ?? Auth::id();

        if (! $userId) {
            return;
        }

        if ($record instanceof Lead || $record instanceof Customer) {
            $record->forceFill(['owner_user_id' => $userId])->save();
        }

        if ($record instanceof Opportunity) {
            $record->forceFill(['responsible_user_id' => $userId])->save();
        }
    }

    private function moveToPool(Model $record, array $payload): void
    {
        if ($record instanceof Lead) {
            app(LeadAssignmentService::class)->release($record, $payload['reason'] ?? '自动化进入公海');
        }

        if ($record instanceof Customer) {
            app(CustomerPoolService::class)->releaseCustomer($record, $payload['reason'] ?? '自动化进入公海');
        }
    }

    private function ownerId(Model $record): ?int
    {
        return match (true) {
            $record instanceof Lead, $record instanceof Customer => $record->owner_user_id,
            $record instanceof Opportunity => $record->responsible_user_id,
            $record instanceof Quote => $record->user_id,
            $record instanceof Order => $record->employee_id,
            $record instanceof Activity => $record->owner_user_id,
            default => $record->created_by ?? null,
        };
    }

    private function targetType(Model $record): string
    {
        return match (true) {
            $record instanceof Lead => 'lead',
            $record instanceof Customer => 'customer',
            $record instanceof Opportunity => 'opportunity',
            $record instanceof Quote => 'quote',
            $record instanceof Order => 'order',
            $record instanceof Activity => 'activity',
            default => str($record::class)->classBasename()->snake()->toString(),
        };
    }
}

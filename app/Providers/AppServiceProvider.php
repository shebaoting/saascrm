<?php

namespace App\Providers;

use App\Models\Activity;
use App\Models\Attachment;
use App\Models\AuditLog;
use App\Models\AutomationRule;
use App\Models\Contact;
use App\Models\Customer;
use App\Models\CustomField;
use App\Models\Lead;
use App\Models\Opportunity;
use App\Models\Order;
use App\Models\OrderExpense;
use App\Models\OrderItem;
use App\Models\OrderPaymentPlan;
use App\Models\Payment;
use App\Models\PipelineStage;
use App\Models\Product;
use App\Models\Quote;
use App\Models\QuoteApprovalRequest;
use App\Models\QuoteItem;
use App\Models\Task;
use App\Models\TenantInvitation;
use App\Services\Crm\ActivityService;
use App\Services\Crm\AutomationService;
use App\Services\Crm\BusinessNumberService;
use App\Services\Crm\DuplicateDetectionService;
use App\Services\Crm\FieldHistoryService;
use App\Services\Crm\LeadAssignmentService;
use App\Services\Crm\LeadScoringService;
use App\Services\Crm\OrderFinanceService;
use App\Services\Crm\PlanLimitService;
use App\Services\Crm\QuoteCalculatorService;
use App\Support\Filament\CrmUi;
use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        CrmUi::configureComponents();

        Quote::creating(function (Quote $quote): void {
            $quote->quote_number = $quote->quote_number ?: app(BusinessNumberService::class)->next((int) $quote->tenant_id, 'quote');
            $quote->user_id = $quote->user_id ?: Auth::id();
        });

        Quote::updated(function (Quote $quote): void {
            if ($quote->wasChanged('status') && $quote->status === 'approved') {
                app(AutomationService::class)->run('quote_approved', $quote);
            }
        });

        QuoteItem::saving(function (QuoteItem $item): void {
            app(QuoteCalculatorService::class)->snapshotItem($item);
        });

        QuoteItem::saved(function (QuoteItem $item): void {
            app(QuoteCalculatorService::class)->recalculate($item->quote);
        });

        QuoteItem::deleted(function (QuoteItem $item): void {
            app(QuoteCalculatorService::class)->recalculate($item->quote);
        });

        Activity::creating(function (Activity $activity): void {
            $activity->occurred_at = $activity->occurred_at ?: now();
            $activity->owner_user_id = $activity->owner_user_id ?: Auth::id();
            $activity->subject = $activity->subject ?: CrmUi::followUpSubject($activity->type);
        });

        Activity::saved(function (Activity $activity): void {
            app(ActivityService::class)->syncTimeline($activity);
        });

        Activity::created(function (Activity $activity): void {
            app(AutomationService::class)->run('activity_created', $activity);
        });

        Order::creating(function (Order $order): void {
            $order->order_number = $order->order_number ?: app(BusinessNumberService::class)->next((int) $order->tenant_id, 'order');
            $order->ordered_at = $order->ordered_at ?: now();
        });

        Order::saving(function (Order $order): void {
            if ($order->order_status === 'completed' && blank($order->completed_at)) {
                $order->completed_at = now();
            }
        });

        Order::updated(function (Order $order): void {
            if ($order->wasChanged('order_status') && $order->order_status === 'completed') {
                app(AutomationService::class)->run('order_completed', $order);
            }
        });

        Lead::created(function (Lead $lead): void {
            $lead = app(LeadScoringService::class)->refresh($lead);

            if (! $lead->owner_user_id) {
                $lead = app(LeadAssignmentService::class)->assignByRules($lead) ?: $lead;
            }

            app(AutomationService::class)->run('lead_created', $lead->refresh());
        });

        Lead::saving(fn (Lead $lead): bool => $this->normalizeContactFields($lead));

        Lead::creating(function (Lead $lead): void {
            $tenantId = (int) ($lead->tenant_id ?: Filament::getTenant()?->getKey());

            if (! $lead->tenant_id && $tenantId) {
                $lead->tenant_id = $tenantId;
            }

            if (Schema::hasColumn($lead->getTable(), 'lead_number')) {
                $lead->lead_number = $lead->lead_number ?: app(BusinessNumberService::class)->next($tenantId, 'lead');
            }

            app(DuplicateDetectionService::class)->assertNoDuplicateOnCreate($lead);
            app(PlanLimitService::class)->assertCanCreate($lead, $lead->tenant);
        });

        Customer::created(function (Customer $customer): void {
            app(AutomationService::class)->run('customer_created', $customer);
        });

        Customer::saving(fn (Customer $customer): bool => $this->normalizeContactFields($customer));

        Customer::creating(function (Customer $customer): void {
            $tenantId = (int) ($customer->tenant_id ?: Filament::getTenant()?->getKey());

            if (! $customer->tenant_id && $tenantId) {
                $customer->tenant_id = $tenantId;
            }

            if (Schema::hasColumn($customer->getTable(), 'customer_number')) {
                $customer->customer_number = $customer->customer_number ?: app(BusinessNumberService::class)->next($tenantId, 'customer');
            }

            app(DuplicateDetectionService::class)->assertNoDuplicateOnCreate($customer);
            app(PlanLimitService::class)->assertCanCreate($customer, $customer->tenant);
        });

        Contact::saving(fn (Contact $contact): bool => $this->normalizeContactFields($contact));

        Opportunity::saving(function (Opportunity $opportunity): void {
            if (! $opportunity->pipeline_stage_id || ($opportunity->exists && ! $opportunity->isDirty('pipeline_stage_id'))) {
                return;
            }

            $stage = PipelineStage::query()
                ->where('tenant_id', $opportunity->tenant_id)
                ->whereKey($opportunity->pipeline_stage_id)
                ->first();

            if (! $stage) {
                return;
            }

            $opportunity->pipeline_id = $stage->pipeline_id;
            $opportunity->probability = $stage->probability;

            if ($stage->stage_type === 'won') {
                $opportunity->closed_at = $opportunity->closed_at ?: now();
                $opportunity->ended_at = $opportunity->ended_at ?: now();
                $opportunity->closed_amount = $opportunity->closed_amount ?: $opportunity->amount;
                $opportunity->forecast_category = 'closed';
            } elseif (in_array($stage->stage_type, ['lost', 'invalid'], true)) {
                $opportunity->ended_at = $opportunity->ended_at ?: now();
            }
        });

        Task::creating(function (Task $task): void {
            $task->creator_id = $task->creator_id ?: Auth::id();
            $task->assignee_id = $task->assignee_id ?: Auth::id() ?: $task->creator_id;
        });

        Task::saving(function (Task $task): void {
            if ($task->status === 'completed' && blank($task->completed_at)) {
                $task->completed_at = now();
            }
        });

        Attachment::creating(function (Attachment $attachment): void {
            if ($attachment->tenant && $attachment->size) {
                app(PlanLimitService::class)->assertStorageAvailable($attachment->tenant, (int) $attachment->size);
            }
        });

        CustomField::creating(function (CustomField $field): void {
            app(PlanLimitService::class)->assertCanCreate($field, $field->tenant);
        });

        AutomationRule::creating(function (AutomationRule $rule): void {
            app(PlanLimitService::class)->assertCanCreate($rule, $rule->tenant);
        });

        TenantInvitation::creating(function (TenantInvitation $invitation): void {
            $invitation->token = $invitation->token ?: Str::random(48);
            $invitation->status = $invitation->status ?: 'pending';
            $invitation->invited_by = $invitation->invited_by
                ?: Auth::id()
                ?: $invitation->tenant?->users()->value('users.id');
            $invitation->expires_at = $invitation->expires_at ?: now()->addDays(7);
        });

        TenantInvitation::created(function (TenantInvitation $invitation): void {
            TenantInvitation::query()
                ->where('tenant_id', $invitation->tenant_id)
                ->whereKeyNot($invitation->getKey())
                ->where('status', 'pending')
                ->when($invitation->email, fn ($query) => $query->where('email', $invitation->email))
                ->when(! $invitation->email && $invitation->phone, fn ($query) => $query->where('phone', $invitation->phone))
                ->update(['status' => 'cancelled']);
        });

        OrderItem::saving(function (OrderItem $item): void {
            app(OrderFinanceService::class)->snapshotItem($item);
        });

        OrderItem::saved(function (OrderItem $item): void {
            app(OrderFinanceService::class)->refresh($item->order);
        });

        OrderItem::deleted(function (OrderItem $item): void {
            app(OrderFinanceService::class)->refresh($item->order);
        });

        Payment::saved(function (Payment $payment): void {
            app(OrderFinanceService::class)->refresh($payment->order);
            app(OrderFinanceService::class)->refreshPaymentPlan($payment->paymentPlan);
        });

        Payment::deleted(function (Payment $payment): void {
            app(OrderFinanceService::class)->refresh($payment->order);
            app(OrderFinanceService::class)->refreshPaymentPlan($payment->paymentPlan);
        });

        OrderPaymentPlan::saved(function (OrderPaymentPlan $plan): void {
            app(OrderFinanceService::class)->refreshPaymentPlan($plan);
        });

        OrderExpense::saved(function (OrderExpense $expense): void {
            app(OrderFinanceService::class)->refresh($expense->order);
        });

        OrderExpense::deleted(function (OrderExpense $expense): void {
            app(OrderFinanceService::class)->refresh($expense->order);
        });

        foreach ([Lead::class, Customer::class, Contact::class, Activity::class, Opportunity::class, Quote::class, QuoteApprovalRequest::class, Order::class, OrderPaymentPlan::class, Payment::class, OrderExpense::class, Product::class] as $model) {
            $model::created(fn (Model $record) => $this->recordAudit('created', $record));
            $model::updated(function (Model $record): void {
                $this->recordAudit('updated', $record);
                app(FieldHistoryService::class)->record($record);
            });
            $model::deleted(fn (Model $record) => $this->recordAudit('deleted', $record));
        }
    }

    private function recordAudit(string $action, Model $record): void
    {
        AuditLog::create([
            'tenant_id' => $record->getAttribute('tenant_id'),
            'user_id' => Auth::id(),
            'action' => $action,
            'model_type' => $record::class,
            'model_id' => $record->getKey(),
            'old_values' => $action === 'updated' ? $record->getOriginal() : null,
            'new_values' => $action === 'deleted' ? null : $record->getAttributes(),
            'ip_address' => request()?->ip(),
            'user_agent' => request()?->userAgent(),
            'created_at' => now(),
        ]);
    }

    private function normalizeContactFields(Model $record): bool
    {
        foreach (['phone', 'contact_phone', 'telephone'] as $field) {
            if ($record->getAttribute($field)) {
                $record->setAttribute($field, preg_replace('/[^\d+]/', '', (string) $record->getAttribute($field)));
            }
        }

        if ($record->getAttribute('email')) {
            $record->setAttribute('email', Str::lower(trim((string) $record->getAttribute('email'))));
        }

        foreach (['name', 'company_name', 'short_name'] as $field) {
            if ($record->getAttribute($field)) {
                $value = preg_replace('/\s+/u', ' ', trim((string) $record->getAttribute($field)));
                $value = str_replace(['（', '）'], ['(', ')'], $value);
                $record->setAttribute($field, $value);
            }
        }

        return true;
    }
}

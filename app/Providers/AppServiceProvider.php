<?php

namespace App\Providers;

use App\Models\Activity;
use App\Models\AuditLog;
use App\Models\AutomationRule;
use App\Models\Customer;
use App\Models\CustomField;
use App\Models\Lead;
use App\Models\Opportunity;
use App\Models\Order;
use App\Models\OrderExpense;
use App\Models\OrderItem;
use App\Models\OrderPaymentPlan;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Quote;
use App\Models\QuoteApprovalRequest;
use App\Models\QuoteItem;
use App\Services\Crm\ActivityService;
use App\Services\Crm\AutomationService;
use App\Services\Crm\DuplicateDetectionService;
use App\Services\Crm\OrderFinanceService;
use App\Services\Crm\PlanLimitService;
use App\Services\Crm\QuoteCalculatorService;
use App\Support\Filament\CrmUi;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
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
            $quote->quote_number = $quote->quote_number ?: 'QT'.now()->format('YmdHis');
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
        });

        Activity::saved(function (Activity $activity): void {
            app(ActivityService::class)->syncTimeline($activity);
        });

        Activity::created(function (Activity $activity): void {
            app(AutomationService::class)->run('activity_created', $activity);
        });

        Order::creating(function (Order $order): void {
            $order->order_number = $order->order_number ?: 'SO'.now()->format('YmdHis');
            $order->ordered_at = $order->ordered_at ?: now();
        });

        Order::updated(function (Order $order): void {
            if ($order->wasChanged('order_status') && $order->order_status === 'completed') {
                app(AutomationService::class)->run('order_completed', $order);
            }
        });

        Lead::created(function (Lead $lead): void {
            app(AutomationService::class)->run('lead_created', $lead);
        });

        Lead::saving(fn (Lead $lead): bool => $this->normalizeContactFields($lead));

        Lead::creating(function (Lead $lead): void {
            app(DuplicateDetectionService::class)->assertNoDuplicateOnCreate($lead);
            app(PlanLimitService::class)->assertCanCreate($lead, $lead->tenant);
        });

        Customer::created(function (Customer $customer): void {
            app(AutomationService::class)->run('customer_created', $customer);
        });

        Customer::saving(fn (Customer $customer): bool => $this->normalizeContactFields($customer));

        Customer::creating(function (Customer $customer): void {
            app(DuplicateDetectionService::class)->assertNoDuplicateOnCreate($customer);
            app(PlanLimitService::class)->assertCanCreate($customer, $customer->tenant);
        });

        \App\Models\Contact::saving(fn (\App\Models\Contact $contact): bool => $this->normalizeContactFields($contact));

        CustomField::creating(function (CustomField $field): void {
            app(PlanLimitService::class)->assertCanCreate($field, $field->tenant);
        });

        AutomationRule::creating(function (AutomationRule $rule): void {
            app(PlanLimitService::class)->assertCanCreate($rule, $rule->tenant);
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

        foreach ([Lead::class, Customer::class, Activity::class, Opportunity::class, Quote::class, QuoteApprovalRequest::class, Order::class, OrderPaymentPlan::class, Payment::class, OrderExpense::class, Product::class] as $model) {
            $model::created(fn (Model $record) => $this->recordAudit('created', $record));
            $model::updated(fn (Model $record) => $this->recordAudit('updated', $record));
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

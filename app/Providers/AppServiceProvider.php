<?php

namespace App\Providers;

use App\Models\Activity;
use App\Models\AuditLog;
use App\Models\Customer;
use App\Models\Lead;
use App\Models\Opportunity;
use App\Models\Order;
use App\Models\OrderExpense;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Quote;
use App\Models\QuoteApprovalRequest;
use App\Models\QuoteItem;
use App\Services\Crm\ActivityService;
use App\Services\Crm\OrderFinanceService;
use App\Services\Crm\QuoteCalculatorService;
use App\Support\Filament\CrmUi;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;

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

        Order::creating(function (Order $order): void {
            $order->order_number = $order->order_number ?: 'SO'.now()->format('YmdHis');
            $order->ordered_at = $order->ordered_at ?: now();
        });

        Payment::saved(function (Payment $payment): void {
            app(OrderFinanceService::class)->refresh($payment->order);
        });

        Payment::deleted(function (Payment $payment): void {
            app(OrderFinanceService::class)->refresh($payment->order);
        });

        OrderExpense::saved(function (OrderExpense $expense): void {
            app(OrderFinanceService::class)->refresh($expense->order);
        });

        OrderExpense::deleted(function (OrderExpense $expense): void {
            app(OrderFinanceService::class)->refresh($expense->order);
        });

        foreach ([Lead::class, Customer::class, Activity::class, Opportunity::class, Quote::class, QuoteApprovalRequest::class, Order::class, Payment::class, OrderExpense::class, Product::class] as $model) {
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
}

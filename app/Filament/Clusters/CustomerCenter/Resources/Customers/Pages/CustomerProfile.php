<?php

namespace App\Filament\Clusters\CustomerCenter\Resources\Customers\Pages;

use App\Filament\Clusters\CustomerCenter\Resources\Customers\CustomerResource;
use App\Models\Activity;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Quote;
use App\Support\Filament\CustomFieldUi;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Resources\Pages\Page;

class CustomerProfile extends Page
{
    use InteractsWithRecord;

    protected static string $resource = CustomerResource::class;

    protected string $view = 'filament.clusters.customer-center.resources.customers.pages.customer-profile';

    protected static ?string $title = '客户360';

    public function mount(int|string $record): void
    {
        $this->record = $this->resolveRecord($record);
    }

    protected function getViewData(): array
    {
        /** @var Customer $customer */
        $customer = $this->getRecord();

        $customer->load([
            'owner',
            'members',
            'contacts',
            'opportunities.stage',
            'quotes',
            'orders',
            'activities.owner',
            'tasks.assignee',
            'attachments',
            'poolHistories',
            'transferHistories',
        ]);

        return [
            'customer' => $customer,
            'metrics' => [
                ['label' => '联系人', 'value' => $customer->contacts->count()],
                ['label' => '商机金额', 'value' => $this->money($customer->opportunities->sum('amount'))],
                ['label' => '报价金额', 'value' => $this->money($customer->quotes->sum('total_amount'))],
                ['label' => '订单金额', 'value' => $this->money($customer->orders->sum('total_amount'))],
            ],
            'customFields' => CustomFieldUi::fields('customer')
                ->map(fn ($field): array => [
                    'label' => $field->name,
                    'value' => CustomFieldUi::displayValue($field, data_get($customer->custom_fields, $field->identifier)) ?: '-',
                ])
                ->values(),
            'timeline' => $customer->activities
                ->sortByDesc('occurred_at')
                ->take(12)
                ->values(),
            'latestQuotes' => $customer->quotes
                ->sortByDesc(fn (Quote $quote) => $quote->created_at)
                ->take(6)
                ->values(),
            'latestOrders' => $customer->orders
                ->sortByDesc(fn (Order $order) => $order->ordered_at)
                ->take(6)
                ->values(),
            'lastActivity' => $customer->activities
                ->sortByDesc(fn (Activity $activity) => $activity->occurred_at)
                ->first(),
        ];
    }

    private function money(mixed $amount): string
    {
        return '¥'.number_format((float) $amount, 2);
    }
}

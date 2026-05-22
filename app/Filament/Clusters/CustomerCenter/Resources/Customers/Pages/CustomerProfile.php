<?php

namespace App\Filament\Clusters\CustomerCenter\Resources\Customers\Pages;

use App\Filament\Clusters\CustomerCenter\Resources\Customers\CustomerResource;
use App\Models\Activity;
use App\Models\Contact;
use App\Models\Customer;
use App\Models\Opportunity;
use App\Models\Order;
use App\Models\Pipeline;
use App\Models\PipelineStage;
use App\Models\Quote;
use App\Models\Task;
use App\Models\User;
use App\Support\Filament\CustomFieldUi;
use App\Support\Filament\CrmUi;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Resources\Pages\Page;
use Illuminate\Database\Eloquent\Builder;

class CustomerProfile extends Page
{
    use InteractsWithRecord;

    protected static string $resource = CustomerResource::class;

    protected string $view = 'filament.clusters.customer-center.resources.customers.pages.customer-profile';

    protected static ?string $title = '客户360';

    public string $activeTab = 'timeline';

    public function mount(int|string $record): void
    {
        $this->record = $this->resolveRecord($record);
    }

    public function setTab(string $tab): void
    {
        $this->activeTab = $tab;
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('createContact')
                ->label('新增联系人')
                ->icon('heroicon-o-user-plus')
                ->form([
                    TextInput::make('name')->required(),
                    TextInput::make('phone')->tel(),
                    TextInput::make('email')->email(),
                    TextInput::make('position'),
                    TextInput::make('department'),
                ])
                ->action(function (array $data): void {
                    /** @var Customer $customer */
                    $customer = $this->getRecord();

                    Contact::create([
                        'tenant_id' => $customer->tenant_id,
                        'customer_id' => $customer->id,
                        'name' => $data['name'],
                        'phone' => $data['phone'] ?? null,
                        'email' => $data['email'] ?? null,
                        'position' => $data['position'] ?? null,
                        'department' => $data['department'] ?? null,
                    ]);

                    Notification::make()->success()->title('联系人已新增')->send();
                }),
            Action::make('createActivity')
                ->label('新增活动')
                ->icon('heroicon-o-chat-bubble-left-right')
                ->form([
                    Select::make('contact_id')
                        ->options(fn (): array => $this->getRecord()->contacts()->orderBy('name')->pluck('name', 'id')->all()),
                    Select::make('type')
                        ->options(CrmUi::options('activity.type'))
                        ->default('note')
                        ->required(),
                    Select::make('direction')
                        ->options(CrmUi::options('activity.direction'))
                        ->default('outgoing'),
                    TextInput::make('subject')->required(),
                    Textarea::make('content')->columnSpanFull(),
                    TextInput::make('outcome'),
                    DateTimePicker::make('occurred_at')->default(now())->required(),
                    DateTimePicker::make('next_follow_at'),
                ])
                ->action(function (array $data): void {
                    /** @var Customer $customer */
                    $customer = $this->getRecord();

                    Activity::create([
                        'tenant_id' => $customer->tenant_id,
                        'customer_id' => $customer->id,
                        'contact_id' => $data['contact_id'] ?? null,
                        'type' => $data['type'],
                        'direction' => $data['direction'] ?? null,
                        'subject' => $data['subject'],
                        'content' => $data['content'] ?? null,
                        'outcome' => $data['outcome'] ?? null,
                        'occurred_at' => $data['occurred_at'],
                        'next_follow_at' => $data['next_follow_at'] ?? null,
                        'owner_user_id' => auth()->id() ?: $customer->owner_user_id,
                    ]);

                    Notification::make()->success()->title('活动已新增')->send();
                }),
            Action::make('createTask')
                ->label('新增任务')
                ->icon('heroicon-o-clipboard-document-check')
                ->form([
                    TextInput::make('title')->required(),
                    Textarea::make('description')->columnSpanFull(),
                    DateTimePicker::make('due_at')->required(),
                    Select::make('priority')
                        ->options(CrmUi::options('task.priority'))
                        ->default('normal')
                        ->required(),
                    Select::make('assignee_id')
                        ->options(fn (): array => User::query()
                            ->whereHas('tenants', fn (Builder $query) => $query->whereKey($this->getRecord()->tenant_id))
                            ->orderBy('name')
                            ->pluck('name', 'id')
                            ->all())
                        ->default(fn (): ?int => auth()->id()),
                ])
                ->action(function (array $data): void {
                    /** @var Customer $customer */
                    $customer = $this->getRecord();

                    Task::create([
                        'tenant_id' => $customer->tenant_id,
                        'customer_id' => $customer->id,
                        'creator_id' => auth()->id(),
                        'assignee_id' => $data['assignee_id'] ?: auth()->id(),
                        'title' => $data['title'],
                        'description' => $data['description'] ?? null,
                        'due_at' => $data['due_at'],
                        'priority' => $data['priority'],
                        'status' => 'not_started',
                    ]);

                    Notification::make()->success()->title('任务已新增')->send();
                }),
            Action::make('createOpportunity')
                ->label('新增商机')
                ->icon('heroicon-o-sparkles')
                ->form([
                    Select::make('pipeline_id')
                        ->options(fn (): array => Pipeline::query()
                            ->where('tenant_id', $this->getRecord()->tenant_id)
                            ->where('is_active', true)
                            ->orderByDesc('is_default')
                            ->pluck('name', 'id')
                            ->all())
                        ->live()
                        ->required(),
                    Select::make('pipeline_stage_id')
                        ->options(fn ($get): array => PipelineStage::query()
                            ->where('tenant_id', $this->getRecord()->tenant_id)
                            ->when($get('pipeline_id'), fn (Builder $query, int|string $pipelineId): Builder => $query->where('pipeline_id', $pipelineId))
                            ->where('is_active', true)
                            ->orderBy('sort_order')
                            ->pluck('name', 'id')
                            ->all())
                        ->required(),
                    TextInput::make('name')->required(),
                    TextInput::make('amount')->numeric()->default(0)->required(),
                    DatePicker::make('expected_close_date'),
                ])
                ->action(function (array $data): void {
                    /** @var Customer $customer */
                    $customer = $this->getRecord();
                    $stage = PipelineStage::find($data['pipeline_stage_id']);

                    Opportunity::create([
                        'tenant_id' => $customer->tenant_id,
                        'customer_id' => $customer->id,
                        'pipeline_id' => $data['pipeline_id'],
                        'pipeline_stage_id' => $data['pipeline_stage_id'],
                        'name' => $data['name'],
                        'amount' => $data['amount'] ?? 0,
                        'probability' => $stage?->probability ?: 0,
                        'forecast_category' => 'pipeline',
                        'expected_close_date' => $data['expected_close_date'] ?? null,
                        'responsible_user_id' => auth()->id() ?: $customer->owner_user_id,
                    ]);

                    Notification::make()->success()->title('商机已新增')->send();
                }),
        ];
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
            'orders.paymentPlans',
            'orders.payments',
            'orders.expenses',
            'activities.owner',
            'tasks.assignee',
            'attachments',
            'poolHistories',
            'transferHistories',
            'fieldHistories',
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
            'tabs' => [
                'timeline' => ['label' => '活动', 'count' => $customer->activities->count()],
                'contacts' => ['label' => '联系人', 'count' => $customer->contacts->count()],
                'opportunities' => ['label' => '商机', 'count' => $customer->opportunities->count()],
                'quotes' => ['label' => '报价', 'count' => $customer->quotes->count()],
                'orders' => ['label' => '订单', 'count' => $customer->orders->count()],
                'tasks' => ['label' => '任务', 'count' => $customer->tasks->count()],
                'attachments' => ['label' => '附件', 'count' => $customer->attachments->count()],
                'history' => ['label' => '变更历史', 'count' => $customer->transferHistories->count() + $customer->poolHistories->count() + $customer->fieldHistories->count()],
            ],
            'timeline' => $customer->activities
                ->sortByDesc('occurred_at')
                ->take(20)
                ->values(),
            'latestQuotes' => $customer->quotes
                ->sortByDesc(fn (Quote $quote) => $quote->created_at)
                ->take(20)
                ->values(),
            'latestOrders' => $customer->orders
                ->sortByDesc(fn (Order $order) => $order->ordered_at)
                ->take(20)
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

<?php

namespace App\Filament\Clusters\CustomerCenter\Resources\Customers\Pages\Concerns;

use App\Models\Activity;
use App\Models\Contact;
use App\Models\Customer;
use App\Models\Opportunity;
use App\Models\Pipeline;
use App\Models\PipelineStage;
use App\Models\Task;
use App\Models\User;
use App\Support\Filament\CrmUi;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Grid;
use Illuminate\Database\Eloquent\Builder;

trait HasCustomerViewActions
{
    protected function getCustomerViewActions(): array
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
                ->label('新增跟进')
                ->icon('heroicon-o-chat-bubble-left-right')
                ->form([
                    Grid::make(['md' => 2])
                        ->schema([
                            Select::make('contact_id')
                                ->label('联系人')
                                ->options(fn (): array => $this->getRecord()->contacts()->orderBy('name')->pluck('name', 'id')->all()),
                            Select::make('type')
                                ->label('跟进类型')
                                ->options(CrmUi::options('activity.type'))
                                ->default('note')
                                ->required(),
                            Select::make('direction')
                                ->label('方向')
                                ->options(CrmUi::options('activity.direction'))
                                ->default('outgoing'),
                            DateTimePicker::make('occurred_at')
                                ->label('跟进时间')
                                ->default(now())
                                ->required(),
                            DateTimePicker::make('next_follow_at')
                                ->label('下次跟进时间'),
                            Textarea::make('content')
                                ->label('跟进内容')
                                ->required()
                                ->rows(4)
                                ->columnSpanFull(),
                        ]),
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
                        'subject' => CrmUi::followUpSubject($data['type'] ?? null),
                        'content' => $data['content'] ?? null,
                        'occurred_at' => $data['occurred_at'],
                        'next_follow_at' => $data['next_follow_at'] ?? null,
                        'owner_user_id' => auth()->id() ?: $customer->owner_user_id,
                    ]);

                    Notification::make()->success()->title('跟进已新增')->send();
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
}

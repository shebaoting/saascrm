<?php

namespace App\Filament\Clusters\CustomerCenter\Resources\Customers\Schemas;

use App\Models\Customer;
use App\Support\Filament\CrmUi;
use App\Support\Filament\CustomFieldUi;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\RepeatableEntry\TableColumn;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CustomerInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('客户档案')
                    ->schema([
                        TextEntry::make('name')
                            ->label('客户名称'),
                        TextEntry::make('customer_number')
                            ->label('客户编号')
                            ->placeholder('-'),
                        TextEntry::make('customer_type')
                            ->label('客户类型')
                            ->formatStateUsing(fn ($state, Customer $record): string => CrmUi::valueLabel('customer_type', $state, $record))
                            ->placeholder('-'),
                        TextEntry::make('lifecycle_stage')
                            ->label('生命周期')
                            ->formatStateUsing(fn ($state, Customer $record): string => CrmUi::valueLabel('lifecycle_stage', $state, $record))
                            ->placeholder('-'),
                        TextEntry::make('owner.name')
                            ->label('负责人')
                            ->placeholder('-'),
                        TextEntry::make('members')
                            ->label('协作人')
                            ->state(fn (Customer $record): string => $record->members()->pluck('name')->join('、') ?: '-'),
                        TextEntry::make('phone')
                            ->label('电话')
                            ->placeholder('-'),
                        TextEntry::make('email')
                            ->label('邮箱')
                            ->placeholder('-'),
                        TextEntry::make('source')
                            ->label('来源')
                            ->placeholder('-'),
                        TextEntry::make('last_activity_at')
                            ->label('最后跟进')
                            ->dateTime()
                            ->placeholder('-'),
                        TextEntry::make('next_activity_at')
                            ->label('下次跟进')
                            ->dateTime()
                            ->placeholder('-'),
                        TextEntry::make('last_order_at')
                            ->label('最近订单')
                            ->dateTime()
                            ->placeholder('-'),
                    ])
                    ->columns(['md' => 4])
                    ->compact()
                    ->columnSpanFull(),
                Section::make('关键指标')
                    ->schema([
                        TextEntry::make('metrics_contacts')
                            ->label('联系人')
                            ->state(fn (Customer $record): int => $record->contacts()->count()),
                        TextEntry::make('metrics_opportunities')
                            ->label('商机金额')
                            ->state(fn (Customer $record): string => self::money($record->opportunities()->sum('amount'))),
                        TextEntry::make('metrics_quotes')
                            ->label('报价金额')
                            ->state(fn (Customer $record): string => self::money($record->quotes()->sum('total_amount'))),
                        TextEntry::make('metrics_orders')
                            ->label('订单金额')
                            ->state(fn (Customer $record): string => self::money($record->orders()->sum('total_amount'))),
                    ])
                    ->columns(['md' => 4])
                    ->compact()
                    ->columnSpanFull(),
                ...CustomFieldUi::infolistSections('customer'),
                Section::make('联系人')
                    ->schema([
                        RepeatableEntry::make('contacts_overview')
                            ->hiddenLabel()
                            ->state(fn (Customer $record): array => $record->contacts()
                                ->orderByDesc('is_primary')
                                ->orderBy('name')
                                ->get()
                                ->map(fn ($contact): array => [
                                    'name' => $contact->name,
                                    'role' => $contact->position ?: $contact->department ?: '-',
                                    'phone' => $contact->phone ?: '-',
                                    'email' => $contact->email ?: '-',
                                ])
                                ->all())
                            ->table([
                                TableColumn::make('姓名'),
                                TableColumn::make('职务/部门'),
                                TableColumn::make('电话'),
                                TableColumn::make('邮箱'),
                            ])
                            ->schema([
                                TextEntry::make('name')->hiddenLabel(),
                                TextEntry::make('role')->hiddenLabel(),
                                TextEntry::make('phone')->hiddenLabel(),
                                TextEntry::make('email')->hiddenLabel(),
                            ])
                            ->placeholder('暂无联系人'),
                    ])
                    ->compact()
                    ->columnSpanFull(),
                Section::make('活动与任务')
                    ->schema([
                        RepeatableEntry::make('activities_overview')
                            ->label('活动时间线')
                            ->state(fn (Customer $record): array => $record->activities()
                                ->with('owner')
                                ->latest('occurred_at')
                                ->limit(12)
                                ->get()
                                ->map(fn ($activity): array => [
                                    'subject' => $activity->subject ?: '未命名活动',
                                    'meta' => CrmUi::valueLabel('type', $activity->type, $activity).' / '.($activity->owner?->name ?: '-'),
                                    'time' => $activity->occurred_at?->format('m-d H:i') ?: '-',
                                ])
                                ->all())
                            ->table([
                                TableColumn::make('主题'),
                                TableColumn::make('类型/负责人'),
                                TableColumn::make('时间'),
                            ])
                            ->schema([
                                TextEntry::make('subject')->hiddenLabel(),
                                TextEntry::make('meta')->hiddenLabel(),
                                TextEntry::make('time')->hiddenLabel(),
                            ])
                            ->placeholder('暂无活动'),
                        RepeatableEntry::make('tasks_overview')
                            ->label('任务')
                            ->state(fn (Customer $record): array => $record->tasks()
                                ->with('assignee')
                                ->orderBy('due_at')
                                ->limit(12)
                                ->get()
                                ->map(fn ($task): array => [
                                    'title' => $task->title,
                                    'meta' => CrmUi::valueLabel('task.status', $task->status, $task).' / '.($task->assignee?->name ?: '-'),
                                    'due_at' => $task->due_at?->format('m-d H:i') ?: '-',
                                ])
                                ->all())
                            ->table([
                                TableColumn::make('任务'),
                                TableColumn::make('状态/负责人'),
                                TableColumn::make('截止时间'),
                            ])
                            ->schema([
                                TextEntry::make('title')->hiddenLabel(),
                                TextEntry::make('meta')->hiddenLabel(),
                                TextEntry::make('due_at')->hiddenLabel(),
                            ])
                            ->placeholder('暂无任务'),
                    ])
                    ->columns(['lg' => 2])
                    ->compact()
                    ->columnSpanFull(),
                Section::make('交易进展')
                    ->schema([
                        RepeatableEntry::make('opportunities_overview')
                            ->label('商机')
                            ->state(fn (Customer $record): array => $record->opportunities()
                                ->with('stage')
                                ->latest('updated_at')
                                ->limit(12)
                                ->get()
                                ->map(fn ($opportunity): array => [
                                    'name' => $opportunity->name,
                                    'stage' => ($opportunity->stage?->name ?: '-').' / '.CrmUi::valueLabel('forecast_category', $opportunity->forecast_category, $opportunity),
                                    'amount' => self::money($opportunity->amount),
                                ])
                                ->all())
                            ->table([
                                TableColumn::make('商机'),
                                TableColumn::make('阶段'),
                                TableColumn::make('金额'),
                            ])
                            ->schema([
                                TextEntry::make('name')->hiddenLabel(),
                                TextEntry::make('stage')->hiddenLabel(),
                                TextEntry::make('amount')->hiddenLabel(),
                            ])
                            ->placeholder('暂无商机'),
                        RepeatableEntry::make('quotes_overview')
                            ->label('报价')
                            ->state(fn (Customer $record): array => $record->quotes()
                                ->latest('created_at')
                                ->limit(12)
                                ->get()
                                ->map(fn ($quote): array => [
                                    'title' => $quote->quote_number ?: $quote->title ?: '-',
                                    'status' => 'V'.$quote->version.' / '.CrmUi::valueLabel('status', $quote->status, $quote),
                                    'amount' => self::money($quote->total_amount),
                                ])
                                ->all())
                            ->table([
                                TableColumn::make('报价'),
                                TableColumn::make('版本/状态'),
                                TableColumn::make('金额'),
                            ])
                            ->schema([
                                TextEntry::make('title')->hiddenLabel(),
                                TextEntry::make('status')->hiddenLabel(),
                                TextEntry::make('amount')->hiddenLabel(),
                            ])
                            ->placeholder('暂无报价'),
                        RepeatableEntry::make('orders_overview')
                            ->label('订单、收款与支出')
                            ->state(fn (Customer $record): array => $record->orders()
                                ->with(['payments', 'expenses'])
                                ->latest('ordered_at')
                                ->limit(12)
                                ->get()
                                ->map(fn ($order): array => [
                                    'number' => $order->order_number,
                                    'meta' => CrmUi::valueLabel('order_status', $order->order_status, $order)
                                        .' / 已收 '.self::money($order->payments->where('status', 'completed')->sum('amount'))
                                        .' / 支出 '.self::money($order->expenses->sum('amount')),
                                    'amount' => self::money($order->total_amount),
                                ])
                                ->all())
                            ->table([
                                TableColumn::make('订单'),
                                TableColumn::make('状态'),
                                TableColumn::make('金额'),
                            ])
                            ->schema([
                                TextEntry::make('number')->hiddenLabel(),
                                TextEntry::make('meta')->hiddenLabel(),
                                TextEntry::make('amount')->hiddenLabel(),
                            ])
                            ->placeholder('暂无订单'),
                    ])
                    ->columns(['lg' => 1])
                    ->compact()
                    ->columnSpanFull(),
                Section::make('附件与历史')
                    ->schema([
                        RepeatableEntry::make('attachments_overview')
                            ->label('附件')
                            ->state(fn (Customer $record): array => $record->attachments()
                                ->latest('created_at')
                                ->limit(12)
                                ->get()
                                ->map(fn ($attachment): array => [
                                    'name' => $attachment->name ?: basename($attachment->path),
                                    'category' => CrmUi::valueLabel('category', $attachment->category, $attachment),
                                    'created_at' => $attachment->created_at?->format('Y-m-d') ?: '-',
                                ])
                                ->all())
                            ->table([
                                TableColumn::make('文件'),
                                TableColumn::make('分类'),
                                TableColumn::make('日期'),
                            ])
                            ->schema([
                                TextEntry::make('name')->hiddenLabel(),
                                TextEntry::make('category')->hiddenLabel(),
                                TextEntry::make('created_at')->hiddenLabel(),
                            ])
                            ->placeholder('暂无附件'),
                        RepeatableEntry::make('history_overview')
                            ->label('变更历史')
                            ->state(fn (Customer $record): array => self::historyRows($record))
                            ->table([
                                TableColumn::make('内容'),
                                TableColumn::make('类型'),
                                TableColumn::make('时间'),
                            ])
                            ->schema([
                                TextEntry::make('name')->hiddenLabel(),
                                TextEntry::make('type')->hiddenLabel(),
                                TextEntry::make('created_at')->hiddenLabel(),
                            ])
                            ->placeholder('暂无历史'),
                    ])
                    ->columns(['lg' => 2])
                    ->compact()
                    ->columnSpanFull(),
                TextEntry::make('created_at')
                    ->label('创建时间')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->label('更新时间')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('deleted_at')
                    ->label('删除时间')
                    ->dateTime()
                    ->visible(fn (Customer $record): bool => $record->trashed()),
            ]);
    }

    private static function money(mixed $amount): string
    {
        return '¥'.number_format((float) $amount, 2);
    }

    private static function historyRows(Customer $record): array
    {
        $poolHistories = $record->poolHistories()
            ->latest('created_at')
            ->limit(8)
            ->get()
            ->map(fn ($history): array => [
                'name' => $history->reason ?: $history->action ?: '客户公海变更',
                'type' => '公海记录',
                'created_at' => $history->created_at?->format('m-d H:i') ?: '-',
                'sort_at' => $history->created_at,
            ]);

        $transferHistories = $record->transferHistories()
            ->latest('created_at')
            ->limit(8)
            ->get()
            ->map(fn ($history): array => [
                'name' => $history->reason ?: $history->action ?: '客户转移',
                'type' => '转移记录',
                'created_at' => $history->created_at?->format('m-d H:i') ?: '-',
                'sort_at' => $history->created_at,
            ]);

        $fieldHistories = $record->fieldHistories()
            ->latest('created_at')
            ->limit(8)
            ->get()
            ->map(function ($history): array {
                $oldValue = data_get($history->old_value, 'value', '-');
                $newValue = data_get($history->new_value, 'value', '-');
                $oldText = is_scalar($oldValue) ? (string) $oldValue : json_encode($oldValue, JSON_UNESCAPED_UNICODE);
                $newText = is_scalar($newValue) ? (string) $newValue : json_encode($newValue, JSON_UNESCAPED_UNICODE);

                return [
                    'name' => $history->field.'：'.$oldText.' -> '.$newText,
                    'type' => '字段变更',
                    'created_at' => $history->created_at?->format('m-d H:i') ?: '-',
                    'sort_at' => $history->created_at,
                ];
            });

        return $poolHistories
            ->merge($transferHistories)
            ->merge($fieldHistories)
            ->sortByDesc('sort_at')
            ->take(12)
            ->map(fn (array $row): array => [
                'name' => $row['name'],
                'type' => $row['type'],
                'created_at' => $row['created_at'],
            ])
            ->values()
            ->all();
    }
}

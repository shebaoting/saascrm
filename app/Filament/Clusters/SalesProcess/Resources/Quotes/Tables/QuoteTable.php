<?php

namespace App\Filament\Clusters\SalesProcess\Resources\Quotes\Tables;

use App\Jobs\GenerateQuotePdfJob;
use App\Models\Notification as CrmNotification;
use App\Models\Quote;
use App\Models\QuoteApprovalRequest;
use App\Models\User;
use App\Services\Crm\ActivityService;
use App\Services\Crm\AuditLogService;
use App\Services\Crm\QuoteCalculatorService;
use App\Services\Crm\QuoteToOrderService;
use App\Services\Crm\QuoteVersionService;
use App\Support\CrmAccess;
use App\Support\Filament\CrmUi;
use App\Support\Filament\CustomFieldUi;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class QuoteTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->columns([
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('quote_number')
                    ->searchable(),
                TextColumn::make('version')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('sourceQuote.quote_number')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('title')
                    ->searchable(),
                TextColumn::make('customer.name')
                    ->searchable(),
                TextColumn::make('contact.name')
                    ->searchable(),
                TextColumn::make('opportunity.name')
                    ->searchable(),
                TextColumn::make('creator.name')
                    ->searchable(),
                TextColumn::make('priceBook.name')
                    ->searchable(),
                TextColumn::make('subtotal_amount')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('discount_amount')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('total_amount')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('total_cost')
                    ->money()
                    ->sortable(),
                TextColumn::make('total_profit')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('total_tax')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('profit_margin')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('status')
                    ->searchable(),
                TextColumn::make('valid_until')
                    ->date()
                    ->sortable(),
                TextColumn::make('pdf_path')
                    ->searchable(),
                TextColumn::make('accepted_at')
                    ->dateTime()
                    ->sortable(),
                ...CustomFieldUi::tableColumns('quote'),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options(CrmUi::options('quote.status')),
                SelectFilter::make('customer_id')
                    ->relationship('customer', 'name'),
                TrashedFilter::make(),
                ...CustomFieldUi::tableFilters('quote'),
            ])
            ->recordActions([
                Action::make('recalculate')
                    ->label('重算金额')
                    ->icon('heroicon-o-calculator')
                    ->action(function (Quote $record): void {
                        app(QuoteCalculatorService::class)->recalculate($record);

                        Notification::make()->success()->title('报价金额已重算')->send();
                    }),
                Action::make('new_version')
                    ->label('复制新版本')
                    ->icon('heroicon-o-document-duplicate')
                    ->visible(fn (Quote $record): bool => CrmAccess::hasPermission('quote.create') && $record->status !== 'accepted')
                    ->requiresConfirmation()
                    ->action(function (Quote $record): void {
                        $newQuote = app(QuoteVersionService::class)->createNewVersion($record);

                        Notification::make()->success()->title('已生成 V'.$newQuote->version.' 报价')->send();
                    }),
                Action::make('request_approval')
                    ->label('提交审批')
                    ->icon('heroicon-o-paper-airplane')
                    ->visible(fn (Quote $record): bool => CrmAccess::hasPermission('quote.update') && in_array($record->status, ['draft', 'rejected'], true))
                    ->form([
                        Select::make('approver_id')
                            ->label('审批人')
                            ->options(fn (Quote $record): array => User::query()
                                ->whereHas('tenants', fn (Builder $query) => $query->whereKey($record->tenant_id))
                                ->orderBy('name')
                                ->pluck('name', 'id')
                                ->all()),
                        Textarea::make('reason')
                            ->label('审批原因')
                            ->maxLength(1000),
                    ])
                    ->action(function (Quote $record, array $data): void {
                        $approval = QuoteApprovalRequest::create([
                            'tenant_id' => $record->tenant_id,
                            'quote_id' => $record->id,
                            'requested_by' => auth()->id(),
                            'approver_id' => $data['approver_id'] ?? null,
                            'status' => 'pending',
                            'reason' => $data['reason'] ?? null,
                            'requested_at' => now(),
                        ]);

                        $previousStatus = $record->status;

                        $record->forceFill(['status' => 'pending_approval'])->save();

                        if ($approval->approver_id) {
                            CrmNotification::create([
                                'id' => (string) Str::uuid(),
                                'tenant_id' => $record->tenant_id,
                                'type' => 'quote_approval_requested',
                                'notifiable_type' => User::class,
                                'notifiable_id' => $approval->approver_id,
                                'data' => json_encode([
                                    'title' => '新的报价审批',
                                    'body' => $record->quote_number.' 需要审批',
                                    'record_type' => Quote::class,
                                    'record_id' => $record->id,
                                ], JSON_UNESCAPED_UNICODE),
                                'created_at' => now(),
                                'updated_at' => now(),
                            ]);
                        }

                        app(ActivityService::class)->recordSystemEvent(
                            $record->tenant_id,
                            '报价提交审批：'.$record->quote_number,
                            $approval->reason,
                            [
                                'customer' => $record->customer,
                                'contact' => $record->contact,
                                'opportunity' => $record->opportunity,
                            ],
                        );

                        app(AuditLogService::class)->record('quote_approval_requested', $record, [
                            'status' => $previousStatus,
                        ], [
                            'status' => 'pending_approval',
                            'approval_id' => $approval->id,
                            'approver_id' => $approval->approver_id,
                        ]);

                        Notification::make()->success()->title('报价已提交审批')->send();
                    }),
                Action::make('approve')
                    ->label('审批通过')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (Quote $record): bool => CrmAccess::hasPermission('quote.approve') && in_array($record->status, ['draft', 'pending_approval'], true))
                    ->requiresConfirmation()
                    ->action(function (Quote $record): void {
                        $approval = $record->approvals()
                            ->where('status', 'pending')
                            ->latest('requested_at')
                            ->first();

                        if ($approval) {
                            $approval->forceFill([
                                'approver_id' => $approval->approver_id ?: auth()->id(),
                                'status' => 'approved',
                                'approved_at' => now(),
                            ])->save();
                        } else {
                            $approval = QuoteApprovalRequest::create([
                                'tenant_id' => $record->tenant_id,
                                'quote_id' => $record->id,
                                'requested_by' => $record->user_id ?: auth()->id(),
                                'approver_id' => auth()->id(),
                                'status' => 'approved',
                                'requested_at' => now(),
                                'approved_at' => now(),
                            ]);
                        }

                        $previousStatus = $record->status;

                        $record->forceFill(['status' => 'approved'])->save();

                        app(ActivityService::class)->recordSystemEvent(
                            $record->tenant_id,
                            '报价审批通过：'.$record->quote_number,
                            null,
                            [
                                'customer' => $record->customer,
                                'contact' => $record->contact,
                                'opportunity' => $record->opportunity,
                            ],
                        );

                        app(AuditLogService::class)->record('quote_approved', $record, [
                            'status' => $previousStatus,
                        ], [
                            'status' => 'approved',
                            'approval_id' => $approval?->id,
                        ]);

                        Notification::make()->success()->title('报价已批准')->send();
                    }),
                Action::make('generate_pdf')
                    ->label('生成 PDF')
                    ->icon('heroicon-o-document-arrow-down')
                    ->action(function (Quote $record): void {
                        GenerateQuotePdfJob::dispatch($record->id);

                        Notification::make()->success()->title('报价 PDF 已加入生成队列')->send();
                    }),
                Action::make('convert_order')
                    ->label('转订单')
                    ->icon('heroicon-o-document-check')
                    ->color('success')
                    ->visible(fn (Quote $record): bool => CrmAccess::hasPermission('quote.convert_order') && $record->status !== 'accepted')
                    ->requiresConfirmation()
                    ->action(function (Quote $record): void {
                        try {
                            app(QuoteToOrderService::class)->convert($record);

                            Notification::make()->success()->title('订单已生成')->send();
                        } catch (ValidationException $exception) {
                            Notification::make()
                                ->danger()
                                ->title('不能转订单')
                                ->body(collect($exception->errors())->flatten()->join("\n"))
                                ->send();
                        }
                    }),
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
                RestoreAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}

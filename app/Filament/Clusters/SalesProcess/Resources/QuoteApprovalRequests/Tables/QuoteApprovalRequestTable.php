<?php

namespace App\Filament\Clusters\SalesProcess\Resources\QuoteApprovalRequests\Tables;

use App\Models\QuoteApprovalRequest;
use App\Services\Crm\ActivityService;
use App\Services\Crm\AuditLogService;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class QuoteApprovalRequestTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('status')
            ->columns([
                TextColumn::make('quote.title')
                    ->searchable(),
                TextColumn::make('requester.name')
                    ->searchable(),
                TextColumn::make('approver.name')
                    ->searchable(),
                TextColumn::make('status')
                    ->searchable(),
                TextColumn::make('reason')
                    ->searchable(),
                TextColumn::make('approval_comment')
                    ->searchable(),
                TextColumn::make('requested_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('approved_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('rejected_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                Action::make('approve')
                    ->label('通过')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (QuoteApprovalRequest $record): bool => $record->status === 'pending')
                    ->requiresConfirmation()
                    ->action(function (QuoteApprovalRequest $record): void {
                        $record->forceFill([
                            'approver_id' => $record->approver_id ?: auth()->id(),
                            'status' => 'approved',
                            'approved_at' => now(),
                        ])->save();

                        $record->quote()->update(['status' => 'approved']);

                        $quote = $record->quote()->with(['customer', 'contact', 'opportunity'])->first();

                        if ($quote) {
                            app(ActivityService::class)->recordSystemEvent(
                                $quote->tenant_id,
                                '报价审批通过：'.$quote->quote_number,
                                $record->approval_comment,
                                [
                                    'customer' => $quote->customer,
                                    'contact' => $quote->contact,
                                    'opportunity' => $quote->opportunity,
                                ],
                            );

                            app(AuditLogService::class)->record('quote_approved', $quote, [
                                'status' => 'pending_approval',
                            ], [
                                'status' => 'approved',
                                'approval_id' => $record->id,
                                'approver_id' => $record->approver_id,
                            ]);
                        }

                        Notification::make()->success()->title('报价审批已通过')->send();
                    }),
                Action::make('reject')
                    ->label('拒绝')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (QuoteApprovalRequest $record): bool => $record->status === 'pending')
                    ->form([
                        TextInput::make('approval_comment')
                            ->label('审批意见')
                            ->maxLength(1000),
                    ])
                    ->action(function (QuoteApprovalRequest $record, array $data): void {
                        $record->forceFill([
                            'approver_id' => $record->approver_id ?: auth()->id(),
                            'status' => 'rejected',
                            'approval_comment' => $data['approval_comment'] ?? null,
                            'rejected_at' => now(),
                        ])->save();

                        $record->quote()->update(['status' => 'rejected']);

                        $quote = $record->quote()->with(['customer', 'contact', 'opportunity'])->first();

                        if ($quote) {
                            app(ActivityService::class)->recordSystemEvent(
                                $quote->tenant_id,
                                '报价审批拒绝：'.$quote->quote_number,
                                $record->approval_comment,
                                [
                                    'customer' => $quote->customer,
                                    'contact' => $quote->contact,
                                    'opportunity' => $quote->opportunity,
                                ],
                            );

                            app(AuditLogService::class)->record('quote_rejected', $quote, [
                                'status' => 'pending_approval',
                            ], [
                                'status' => 'rejected',
                                'approval_id' => $record->id,
                                'approver_id' => $record->approver_id,
                            ]);
                        }

                        Notification::make()->success()->title('报价审批已拒绝')->send();
                    }),
                ViewAction::make(),
            ])
            ->toolbarActions([]);
    }
}

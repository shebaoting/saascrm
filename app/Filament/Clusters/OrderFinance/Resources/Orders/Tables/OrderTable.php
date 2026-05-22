<?php

namespace App\Filament\Clusters\OrderFinance\Resources\Orders\Tables;

use App\Models\Order;
use App\Services\Crm\OrderFinanceService;
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
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class OrderTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('order_number')
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
                TextColumn::make('order_number')
                    ->searchable(),
                TextColumn::make('customer.name')
                    ->searchable(),
                TextColumn::make('contact.name')
                    ->searchable(),
                TextColumn::make('opportunity.name')
                    ->searchable(),
                TextColumn::make('quote.title')
                    ->searchable(),
                TextColumn::make('employee.name')
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
                TextColumn::make('gross_profit')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('order_source')
                    ->searchable(),
                TextColumn::make('order_status')
                    ->searchable(),
                TextColumn::make('payment_status')
                    ->searchable(),
                TextColumn::make('ordered_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('completed_at')
                    ->dateTime()
                    ->sortable(),
                ...CustomFieldUi::tableColumns('order'),
            ])
            ->filters([
                SelectFilter::make('order_status')
                    ->options(CrmUi::options('order.order_status')),
                SelectFilter::make('payment_status')
                    ->options(CrmUi::options('payment_status')),
                SelectFilter::make('customer_id')
                    ->relationship('customer', 'name'),
                TrashedFilter::make(),
                ...CustomFieldUi::tableFilters('order'),
            ])
            ->recordActions([
                Action::make('refresh_finance')
                    ->label('刷新财务')
                    ->icon('heroicon-o-arrow-path')
                    ->action(function (Order $record): void {
                        app(OrderFinanceService::class)->refresh($record);

                        Notification::make()->success()->title('订单财务已刷新')->send();
                    }),
                Action::make('complete')
                    ->label('完成订单')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (Order $record): bool => $record->order_status !== 'completed')
                    ->requiresConfirmation()
                    ->action(function (Order $record): void {
                        $record->forceFill([
                            'order_status' => 'completed',
                            'completed_at' => now(),
                        ])->save();

                        Notification::make()->success()->title('订单已完成')->send();
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

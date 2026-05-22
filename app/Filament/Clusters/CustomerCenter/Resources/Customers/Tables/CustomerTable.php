<?php

namespace App\Filament\Clusters\CustomerCenter\Resources\Customers\Tables;

use App\Models\Customer;
use App\Services\Crm\CustomerMergeService;
use App\Services\Crm\CustomerPoolService;
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
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class CustomerTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
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
                TextColumn::make('customer_number')
                    ->label('客户编号')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('short_name')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('customer_type')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('lifecycle_stage')
                    ->searchable(),
                TextColumn::make('owner.name')
                    ->searchable(),
                TextColumn::make('source')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('country_code')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('area_id')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('email')
                    ->label('邮箱')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('phone')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('pool_entered_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('last_activity_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('next_activity_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('first_order_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('last_order_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                ...CustomFieldUi::tableColumns('customer'),
            ])
            ->filters([
                SelectFilter::make('lifecycle_stage')
                    ->options(CrmUi::options('customer.lifecycle_stage')),
                SelectFilter::make('customer_type')
                    ->options(CrmUi::options('customer.customer_type')),
                SelectFilter::make('owner_user_id')
                    ->relationship('owner', 'name'),
                TrashedFilter::make(),
                ...CustomFieldUi::tableFilters('customer'),
            ])
            ->recordActions([
                Action::make('claim')
                    ->label('领取')
                    ->icon('heroicon-o-hand-raised')
                    ->visible(fn (Customer $record): bool => CrmAccess::hasPermission('customer.claim') && (blank($record->owner_user_id) || $record->lifecycle_stage === 'pooled'))
                    ->action(function (Customer $record): void {
                        app(CustomerPoolService::class)->claimCustomer($record, auth()->user());

                        Notification::make()->success()->title('客户已领取')->send();
                    }),
                Action::make('release')
                    ->label('释放到公海')
                    ->icon('heroicon-o-archive-box-arrow-down')
                    ->color('gray')
                    ->visible(fn (Customer $record): bool => CrmAccess::hasPermission('customer.recycle') && filled($record->owner_user_id))
                    ->form([
                        TextInput::make('reason')
                            ->label('释放原因')
                            ->maxLength(255),
                    ])
                    ->action(function (Customer $record, array $data): void {
                        app(CustomerPoolService::class)->releaseCustomer($record, $data['reason'] ?? '手动释放');

                        Notification::make()->success()->title('客户已进入公海')->send();
                    }),
                Action::make('merge')
                    ->label('合并客户')
                    ->icon('heroicon-o-arrows-right-left')
                    ->color('warning')
                    ->visible(fn (): bool => CrmAccess::hasPermission('customer.merge'))
                    ->form([
                        Select::make('target_customer_id')
                            ->label('合并到')
                            ->options(fn (Customer $record): array => Customer::query()
                                ->where('tenant_id', $record->tenant_id)
                                ->whereKeyNot($record->getKey())
                                ->orderBy('name')
                                ->pluck('name', 'id')
                                ->all())
                            ->searchable()
                            ->required(),
                        Select::make('phone')
                            ->label('电话保留')
                            ->options(['target' => '保留目标客户', 'source' => '保留当前客户'])
                            ->default('target'),
                        Select::make('email')
                            ->label('邮箱保留')
                            ->options(['target' => '保留目标客户', 'source' => '保留当前客户'])
                            ->default('target'),
                    ])
                    ->requiresConfirmation()
                    ->action(function (Customer $record, array $data): void {
                        $target = Customer::findOrFail($data['target_customer_id']);
                        app(CustomerMergeService::class)->mergeWithFields($record, $target, [
                            'phone' => $data['phone'] ?? 'target',
                            'email' => $data['email'] ?? 'target',
                        ]);

                        Notification::make()->success()->title('客户已合并')->send();
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

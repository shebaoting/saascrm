<?php

namespace App\Filament\Clusters\LeadCenter\Resources\Leads\Tables;

use App\Models\Lead;
use App\Services\Crm\LeadAssignmentService;
use App\Services\Crm\LeadConversionService;
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
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class LeadTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('company_name')
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
                TextColumn::make('lead_number')
                    ->label('线索编号')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('company_name')
                    ->searchable(),
                TextColumn::make('contact_name')
                    ->searchable(),
                TextColumn::make('phone')
                    ->searchable(),
                TextColumn::make('email')
                    ->label('邮箱')
                    ->searchable(),
                TextColumn::make('wechat_id')
                    ->searchable(),
                TextColumn::make('country_code')
                    ->searchable(),
                TextColumn::make('area_id')
                    ->searchable(),
                TextColumn::make('address')
                    ->searchable(),
                TextColumn::make('source')
                    ->searchable(),
                TextColumn::make('status')
                    ->searchable(),
                TextColumn::make('qualification_status')
                    ->searchable(),
                TextColumn::make('score')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('owner.name')
                    ->searchable(),
                TextColumn::make('pool_entered_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('last_activity_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('next_activity_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('convertedCustomer.name')
                    ->searchable(),
                TextColumn::make('converted_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('convertedBy.name')
                    ->searchable(),
                TextColumn::make('lost_reason')
                    ->searchable(),
                ...CustomFieldUi::tableColumns('lead'),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options(CrmUi::options('lead.status')),
                SelectFilter::make('qualification_status')
                    ->options(CrmUi::options('lead.qualification_status')),
                SelectFilter::make('owner_user_id')
                    ->relationship('owner', 'name'),
                TrashedFilter::make(),
                ...CustomFieldUi::tableFilters('lead'),
            ])
            ->recordActions([
                Action::make('claim')
                    ->label('领取')
                    ->icon('heroicon-o-hand-raised')
                    ->visible(fn (Lead $record): bool => CrmAccess::hasPermission('lead.claim') && (blank($record->owner_user_id) || in_array($record->status, ['unassigned', 'pooled'], true)))
                    ->action(function (Lead $record): void {
                        app(LeadAssignmentService::class)->claim($record, auth()->user());

                        Notification::make()->success()->title('线索已领取')->send();
                    }),
                Action::make('convert')
                    ->label('转客户')
                    ->icon('heroicon-o-arrow-path-rounded-square')
                    ->visible(fn (Lead $record): bool => CrmAccess::hasPermission('lead.convert') && $record->status !== 'converted')
                    ->form([
                        Toggle::make('create_opportunity')
                            ->label('同时创建商机')
                            ->default(true),
                        TextInput::make('opportunity_name')
                            ->label('商机名称')
                            ->maxLength(255),
                    ])
                    ->action(function (Lead $record, array $data): void {
                        app(LeadConversionService::class)->convert(
                            $record,
                            (bool) ($data['create_opportunity'] ?? false),
                            $data['opportunity_name'] ?? null,
                        );

                        Notification::make()->success()->title('线索已转为客户')->send();
                    }),
                Action::make('release')
                    ->label('释放到公海')
                    ->icon('heroicon-o-archive-box-arrow-down')
                    ->color('gray')
                    ->visible(fn (): bool => CrmAccess::hasPermission('lead.update'))
                    ->requiresConfirmation()
                    ->action(function (Lead $record): void {
                        app(LeadAssignmentService::class)->release($record, '手动释放');

                        Notification::make()->success()->title('线索已进入公海')->send();
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

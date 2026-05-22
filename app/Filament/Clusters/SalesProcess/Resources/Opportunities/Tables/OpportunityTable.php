<?php

namespace App\Filament\Clusters\SalesProcess\Resources\Opportunities\Tables;

use App\Models\Opportunity;
use App\Models\PipelineStage;
use App\Services\Crm\OpportunityStageService;
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

class OpportunityTable
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
                TextColumn::make('customer.name')
                    ->searchable(),
                TextColumn::make('contact.name')
                    ->searchable(),
                TextColumn::make('pipeline.name')
                    ->searchable(),
                TextColumn::make('stage.name')
                    ->searchable(),
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('amount')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('closed_amount')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('probability')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('forecast_category')
                    ->searchable(),
                TextColumn::make('expected_close_date')
                    ->date()
                    ->sortable(),
                TextColumn::make('responsible.name')
                    ->searchable(),
                TextColumn::make('closed_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('ended_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('lost_reason')
                    ->searchable(),
                TextColumn::make('lost_remarks')
                    ->searchable(),
                TextColumn::make('invalid_reason')
                    ->searchable(),
                TextColumn::make('invalid_remarks')
                    ->searchable(),
                TextColumn::make('notes')
                    ->searchable(),
                ...CustomFieldUi::tableColumns('opportunity'),
            ])
            ->filters([
                SelectFilter::make('pipeline_id')
                    ->relationship('pipeline', 'name'),
                SelectFilter::make('pipeline_stage_id')
                    ->relationship('stage', 'name'),
                SelectFilter::make('responsible_user_id')
                    ->relationship('responsible', 'name'),
                TrashedFilter::make(),
                ...CustomFieldUi::tableFilters('opportunity'),
            ])
            ->recordActions([
                Action::make('move_stage')
                    ->label('推进阶段')
                    ->icon('heroicon-o-arrow-right-circle')
                    ->form([
                        Select::make('pipeline_stage_id')
                            ->label('目标阶段')
                            ->options(fn (Opportunity $record): array => PipelineStage::query()
                                ->where('tenant_id', $record->tenant_id)
                                ->where('pipeline_id', $record->pipeline_id)
                                ->where('is_active', true)
                                ->orderBy('sort_order')
                                ->pluck('name', 'id')
                                ->all())
                            ->required(),
                        TextInput::make('notes')
                            ->label('备注')
                            ->maxLength(1000),
                    ])
                    ->action(function (Opportunity $record, array $data): void {
                        app(OpportunityStageService::class)->move(
                            $record,
                            PipelineStage::findOrFail($data['pipeline_stage_id']),
                            $data['notes'] ?? null,
                        );

                        Notification::make()->success()->title('商机阶段已更新')->send();
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

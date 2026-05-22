<?php

namespace App\Filament\Clusters\SalesProcess\Resources\Opportunities;

use App\Filament\Clusters\SalesProcess\Resources\Opportunities\Pages\ManageOpportunities;
use App\Filament\Clusters\SalesProcess\SalesProcessCluster;
use App\Models\Opportunity;
use App\Models\PipelineStage;
use App\Services\Crm\OpportunityStageService;
use App\Support\Filament\CrmUi;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class OpportunityResource extends Resource
{
    protected static ?string $model = Opportunity::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $navigationLabel = '商机';

    protected static ?string $modelLabel = '商机';

    protected static ?string $pluralModelLabel = '商机';

    protected static ?string $title = '商机';

    protected static bool $hasTitleCaseModelLabel = false;

    protected static ?string $cluster = SalesProcessCluster::class;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('customer_id')
                    ->relationship('customer', 'name')
                    ->required(),
                Select::make('contact_id')
                    ->relationship('contact', 'name'),
                Select::make('pipeline_id')
                    ->relationship('pipeline', 'name')
                    ->required(),
                Select::make('pipeline_stage_id')
                    ->relationship('stage', 'name')
                    ->required(),
                TextInput::make('name')
                    ->required(),
                TextInput::make('amount')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('closed_amount')
                    ->numeric(),
                TextInput::make('probability')
                    ->required()
                    ->numeric()
                    ->default(0),
                Select::make('forecast_category')
                    ->options(CrmUi::options('forecast_category'))
                    ->required()
                    ->default('pipeline'),
                DatePicker::make('expected_close_date'),
                Select::make('responsible_user_id')
                    ->relationship('responsible', 'name'),
                DateTimePicker::make('closed_at'),
                DateTimePicker::make('ended_at'),
                TextInput::make('lost_reason'),
                TextInput::make('lost_remarks'),
                TextInput::make('invalid_reason'),
                TextInput::make('invalid_remarks'),
                TextInput::make('notes'),
            ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('deleted_at')
                    ->dateTime()
                    ->visible(fn (Opportunity $record): bool => $record->trashed()),
                TextEntry::make('customer.name')
                    ->label('客户'),
                TextEntry::make('contact.name')
                    ->label('联系人')
                    ->placeholder('-'),
                TextEntry::make('pipeline.name')
                    ->label('销售管道'),
                TextEntry::make('stage.name'),
                TextEntry::make('name'),
                TextEntry::make('amount')
                    ->numeric(),
                TextEntry::make('closed_amount')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('probability')
                    ->numeric(),
                TextEntry::make('forecast_category'),
                TextEntry::make('expected_close_date')
                    ->date()
                    ->placeholder('-'),
                TextEntry::make('responsible.name')
                    ->placeholder('-'),
                TextEntry::make('closed_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('ended_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('lost_reason')
                    ->placeholder('-'),
                TextEntry::make('lost_remarks')
                    ->placeholder('-'),
                TextEntry::make('invalid_reason')
                    ->placeholder('-'),
                TextEntry::make('invalid_remarks')
                    ->placeholder('-'),
                TextEntry::make('notes')
                    ->placeholder('-'),
            ]);
    }

    public static function table(Table $table): Table
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
            ])
            ->filters([
                TrashedFilter::make(),
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
                ForceDeleteAction::make(),
                RestoreAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageOpportunities::route('/'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}

<?php

namespace App\Filament\Clusters\ActivityTasks\Resources\Tasks;

use App\Filament\Clusters\ActivityTasks\ActivityTasksCluster;
use App\Filament\Clusters\ActivityTasks\Resources\Tasks\Pages\ManageTasks;
use App\Models\Task;
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
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
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

class TaskResource extends Resource
{
    protected static ?string $model = Task::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $navigationLabel = '任务';

    protected static ?string $modelLabel = '任务';

    protected static ?string $pluralModelLabel = '任务';

    protected static ?string $title = '任务';

    protected static bool $hasTitleCaseModelLabel = false;

    protected static ?string $cluster = ActivityTasksCluster::class;

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('lead_id')
                    ->relationship('lead', 'id'),
                Select::make('customer_id')
                    ->relationship('customer', 'name'),
                Select::make('contact_id')
                    ->relationship('contact', 'name'),
                Select::make('opportunity_id')
                    ->relationship('opportunity', 'name'),
                TextInput::make('title')
                    ->required(),
                Textarea::make('description')
                    ->columnSpanFull(),
                DateTimePicker::make('start_at'),
                DateTimePicker::make('due_at'),
                DateTimePicker::make('completed_at'),
                Select::make('creator_id')
                    ->relationship('creator', 'name')
                    ->default(fn (): ?int => auth()->id())
                    ->required(),
                Select::make('assignee_id')
                    ->relationship('assignee', 'name')
                    ->default(fn (): ?int => auth()->id())
                    ->required(),
                Select::make('status')
                    ->options([
                        'not_started' => '未开始',
                        'in_progress' => '进行中',
                        'completed' => '已完成',
                        'ignored' => '已忽略',
                        'cancelled' => '已取消',
                    ])
                    ->required()
                    ->default('not_started'),
                Select::make('priority')
                    ->options([
                        'low' => '低',
                        'normal' => '普通',
                        'high' => '高',
                        'urgent' => '紧急',
                    ])
                    ->required()
                    ->default('normal'),
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
                    ->visible(fn (Task $record): bool => $record->trashed()),
                TextEntry::make('lead.id')
                    ->label('Lead')
                    ->placeholder('-'),
                TextEntry::make('customer.name')
                    ->label('Customer')
                    ->placeholder('-'),
                TextEntry::make('contact.name')
                    ->label('Contact')
                    ->placeholder('-'),
                TextEntry::make('opportunity.name')
                    ->label('Opportunity')
                    ->placeholder('-'),
                TextEntry::make('title'),
                TextEntry::make('description')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('start_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('due_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('completed_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('creator.name')
                    ->label('Creator'),
                TextEntry::make('assignee.name')
                    ->label('Assignee'),
                TextEntry::make('status'),
                TextEntry::make('priority'),
            ]);
    }

    public static function table(Table $table): Table
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
                TextColumn::make('lead.id')
                    ->searchable(),
                TextColumn::make('customer.name')
                    ->searchable(),
                TextColumn::make('contact.name')
                    ->searchable(),
                TextColumn::make('opportunity.name')
                    ->searchable(),
                TextColumn::make('title')
                    ->searchable(),
                TextColumn::make('start_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('due_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('completed_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('creator.name')
                    ->searchable(),
                TextColumn::make('assignee.name')
                    ->searchable(),
                TextColumn::make('status')
                    ->searchable(),
                TextColumn::make('priority')
                    ->searchable(),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->recordActions([
                Action::make('complete')
                    ->label('完成')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (Task $record): bool => $record->status !== 'completed')
                    ->action(function (Task $record): void {
                        $record->forceFill([
                            'status' => 'completed',
                            'completed_at' => now(),
                        ])->save();

                        Notification::make()->success()->title('任务已完成')->send();
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
            'index' => ManageTasks::route('/'),
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

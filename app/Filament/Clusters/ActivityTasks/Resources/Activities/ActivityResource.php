<?php

namespace App\Filament\Clusters\ActivityTasks\Resources\Activities;

use App\Filament\Clusters\ActivityTasks\ActivityTasksCluster;
use App\Filament\Clusters\ActivityTasks\Resources\Activities\Pages\ManageActivities;
use App\Models\Activity;
use BackedEnum;
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
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ActivityResource extends Resource
{
    protected static ?string $model = Activity::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $navigationLabel = '活动时间线';

    protected static ?string $modelLabel = '活动时间线';

    protected static ?string $pluralModelLabel = '活动时间线';

    protected static ?string $title = '活动时间线';

    protected static bool $hasTitleCaseModelLabel = false;

    protected static ?string $cluster = ActivityTasksCluster::class;

    protected static ?string $recordTitleAttribute = 'subject';

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
                TextInput::make('type')
                    ->required()
                    ->default('note'),
                TextInput::make('direction'),
                TextInput::make('subject'),
                Textarea::make('content')
                    ->columnSpanFull(),
                TextInput::make('outcome'),
                DateTimePicker::make('occurred_at')
                    ->required(),
                DateTimePicker::make('next_follow_at'),
                Select::make('owner_user_id')
                    ->relationship('owner', 'name')
                    ->default(fn (): ?int => auth()->id())
                    ->required(),
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
                    ->visible(fn (Activity $record): bool => $record->trashed()),
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
                TextEntry::make('type'),
                TextEntry::make('direction')
                    ->placeholder('-'),
                TextEntry::make('subject')
                    ->placeholder('-'),
                TextEntry::make('content')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('outcome')
                    ->placeholder('-'),
                TextEntry::make('occurred_at')
                    ->dateTime(),
                TextEntry::make('next_follow_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('owner_user_id')
                    ->numeric(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('subject')
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
                TextColumn::make('type')
                    ->searchable(),
                TextColumn::make('direction')
                    ->searchable(),
                TextColumn::make('subject')
                    ->searchable(),
                TextColumn::make('outcome')
                    ->searchable(),
                TextColumn::make('occurred_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('next_follow_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('owner_user_id')
                    ->numeric()
                    ->sortable(),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->recordActions([
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
            'index' => ManageActivities::route('/'),
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

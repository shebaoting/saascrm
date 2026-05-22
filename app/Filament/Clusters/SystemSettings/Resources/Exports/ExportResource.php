<?php

namespace App\Filament\Clusters\SystemSettings\Resources\Exports;

use App\Filament\Clusters\SystemSettings\Resources\Exports\Pages\ManageExports;
use App\Filament\Clusters\SystemSettings\SystemSettingsCluster;
use App\Filament\Concerns\UsesCrmAccess;
use App\Models\Export;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ExportResource extends Resource
{
    use UsesCrmAccess;

    protected static ?string $model = Export::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $navigationLabel = '导出任务';

    protected static ?string $modelLabel = '导出任务';

    protected static ?string $pluralModelLabel = '导出任务';

    protected static ?string $title = '导出任务';

    protected static bool $hasTitleCaseModelLabel = false;

    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $cluster = SystemSettingsCluster::class;

    protected static ?string $recordTitleAttribute = 'exporter';

    public static function canCreate(): bool
    {
        return false;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                DateTimePicker::make('completed_at'),
                TextInput::make('file_disk')
                    ->required()
                    ->default('local'),
                TextInput::make('file_name'),
                TextInput::make('exporter')
                    ->required(),
                TextInput::make('processed_rows')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('total_rows')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('successful_rows')
                    ->required()
                    ->numeric()
                    ->default(0),
                Select::make('user_id')
                    ->relationship('user', 'name')
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
                TextEntry::make('completed_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('file_disk'),
                TextEntry::make('file_name')
                    ->placeholder('-'),
                TextEntry::make('exporter'),
                TextEntry::make('processed_rows')
                    ->numeric(),
                TextEntry::make('total_rows')
                    ->numeric(),
                TextEntry::make('successful_rows')
                    ->numeric(),
                TextEntry::make('user.name'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('exporter')
            ->columns([
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('completed_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('file_disk')
                    ->searchable(),
                TextColumn::make('file_name')
                    ->searchable(),
                TextColumn::make('exporter')
                    ->searchable(),
                TextColumn::make('processed_rows')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('total_rows')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('successful_rows')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('user.name')
                    ->searchable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
            ])
            ->toolbarActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageExports::route('/'),
        ];
    }
}

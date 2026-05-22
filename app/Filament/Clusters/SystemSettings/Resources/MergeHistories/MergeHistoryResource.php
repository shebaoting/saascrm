<?php

namespace App\Filament\Clusters\SystemSettings\Resources\MergeHistories;

use App\Filament\Clusters\SystemSettings\Resources\MergeHistories\Pages\ManageMergeHistories;
use App\Filament\Clusters\SystemSettings\SystemSettingsCluster;
use App\Models\MergeHistory;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class MergeHistoryResource extends Resource
{
    protected static ?string $model = MergeHistory::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $navigationLabel = '合并历史';

    protected static ?string $modelLabel = '合并历史';

    protected static ?string $pluralModelLabel = '合并历史';

    protected static ?string $title = '合并历史';

    protected static bool $hasTitleCaseModelLabel = false;

    protected static ?string $cluster = SystemSettingsCluster::class;

    protected static ?string $recordTitleAttribute = 'model_type';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('model_type')
                    ->required(),
                TextInput::make('source_id')
                    ->required()
                    ->numeric(),
                TextInput::make('target_id')
                    ->required()
                    ->numeric(),
                TextInput::make('merged_fields'),
                TextInput::make('merged_relations'),
                TextInput::make('merged_by')
                    ->required()
                    ->numeric(),
            ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('model_type'),
                TextEntry::make('source_id')
                    ->numeric(),
                TextEntry::make('target_id')
                    ->numeric(),
                TextEntry::make('merged_by')
                    ->numeric(),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('model_type')
            ->columns([
                TextColumn::make('model_type')
                    ->searchable(),
                TextColumn::make('source_id')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('target_id')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('merged_by')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageMergeHistories::route('/'),
        ];
    }
}

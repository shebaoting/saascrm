<?php

namespace App\Filament\Clusters\SystemSettings\Resources\CustomFieldLayouts;

use App\Filament\Clusters\SystemSettings\Resources\CustomFieldLayouts\Pages\ManageCustomFieldLayouts;
use App\Filament\Clusters\SystemSettings\SystemSettingsCluster;
use App\Models\CustomFieldLayout;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CustomFieldLayoutResource extends Resource
{
    protected static ?string $model = CustomFieldLayout::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $navigationLabel = '字段布局';

    protected static ?string $modelLabel = '字段布局';

    protected static ?string $pluralModelLabel = '字段布局';

    protected static ?string $title = '字段布局';

    protected static bool $hasTitleCaseModelLabel = false;

    protected static ?string $cluster = SystemSettingsCluster::class;

    protected static ?string $recordTitleAttribute = 'model_type';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('model_type')
                    ->required(),
                Select::make('role_id')
                    ->relationship('role', 'name'),
                TextInput::make('layout')
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
                TextEntry::make('model_type'),
                TextEntry::make('role.name')
                    ->label('Role')
                    ->placeholder('-'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('model_type')
            ->columns([
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('model_type')
                    ->searchable(),
                TextColumn::make('role.name')
                    ->searchable(),
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
            'index' => ManageCustomFieldLayouts::route('/'),
        ];
    }
}

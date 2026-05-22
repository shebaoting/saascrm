<?php

namespace App\Filament\Clusters\SystemSettings\Resources\CustomFields;

use App\Filament\Clusters\SystemSettings\Resources\CustomFields\Pages\ManageCustomFields;
use App\Filament\Clusters\SystemSettings\SystemSettingsCluster;
use App\Filament\Concerns\UsesCrmAccess;
use App\Models\CustomField;
use App\Support\Filament\CrmUi;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CustomFieldResource extends Resource
{
    use UsesCrmAccess;

    protected static ?string $model = CustomField::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $navigationLabel = '字段设置';

    protected static ?string $modelLabel = '字段设置';

    protected static ?string $pluralModelLabel = '字段设置';

    protected static ?string $title = '字段设置';

    protected static bool $hasTitleCaseModelLabel = false;

    protected static ?string $cluster = SystemSettingsCluster::class;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('model_type')
                    ->options(CrmUi::options('target_type'))
                    ->required(),
                TextInput::make('group_name'),
                Select::make('type')
                    ->options(CrmUi::options('custom_field.type'))
                    ->required(),
                TextInput::make('identifier')
                    ->required(),
                TextInput::make('name')
                    ->required(),
                TextInput::make('sort_order')
                    ->numeric(),
                Toggle::make('is_visible')
                    ->required(),
                Toggle::make('is_required')
                    ->required(),
                Toggle::make('is_filterable')
                    ->required(),
                Toggle::make('is_list_visible')
                    ->required(),
                Toggle::make('is_show_in_tracking')
                    ->required(),
                KeyValue::make('data')
                    ->columnSpanFull(),
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
                TextEntry::make('group_name')
                    ->placeholder('-'),
                TextEntry::make('type'),
                TextEntry::make('identifier'),
                TextEntry::make('name'),
                TextEntry::make('sort_order')
                    ->numeric()
                    ->placeholder('-'),
                IconEntry::make('is_visible')
                    ->boolean(),
                IconEntry::make('is_required')
                    ->boolean(),
                IconEntry::make('is_filterable')
                    ->boolean(),
                IconEntry::make('is_list_visible')
                    ->boolean(),
                IconEntry::make('is_show_in_tracking')
                    ->boolean(),
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
                TextColumn::make('model_type')
                    ->searchable(),
                TextColumn::make('group_name')
                    ->searchable(),
                TextColumn::make('type')
                    ->searchable(),
                TextColumn::make('identifier')
                    ->searchable(),
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('sort_order')
                    ->numeric()
                    ->sortable(),
                IconColumn::make('is_visible')
                    ->boolean(),
                IconColumn::make('is_required')
                    ->boolean(),
                IconColumn::make('is_filterable')
                    ->boolean(),
                IconColumn::make('is_list_visible')
                    ->boolean(),
                IconColumn::make('is_show_in_tracking')
                    ->boolean(),
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
            'index' => ManageCustomFields::route('/'),
        ];
    }
}

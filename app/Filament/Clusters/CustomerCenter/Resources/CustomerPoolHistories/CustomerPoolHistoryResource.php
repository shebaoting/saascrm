<?php

namespace App\Filament\Clusters\CustomerCenter\Resources\CustomerPoolHistories;

use App\Filament\Clusters\CustomerCenter\CustomerCenterCluster;
use App\Filament\Clusters\CustomerCenter\Resources\CustomerPoolHistories\Pages\ManageCustomerPoolHistories;
use App\Models\CustomerPoolHistory;
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

class CustomerPoolHistoryResource extends Resource
{
    protected static ?string $model = CustomerPoolHistory::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $navigationLabel = '公海历史';

    protected static ?string $modelLabel = '公海历史';

    protected static ?string $pluralModelLabel = '公海历史';

    protected static ?string $title = '公海历史';

    protected static bool $hasTitleCaseModelLabel = false;

    protected static ?string $cluster = CustomerCenterCluster::class;

    protected static ?string $recordTitleAttribute = 'target_type';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('target_type')
                    ->required(),
                TextInput::make('target_id')
                    ->required()
                    ->numeric(),
                TextInput::make('action')
                    ->required(),
                TextInput::make('from_user_id')
                    ->numeric(),
                TextInput::make('to_user_id')
                    ->numeric(),
                TextInput::make('reason'),
                TextInput::make('operated_by')
                    ->numeric(),
            ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('target_type'),
                TextEntry::make('target_id')
                    ->numeric(),
                TextEntry::make('action'),
                TextEntry::make('from_user_id')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('to_user_id')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('reason')
                    ->placeholder('-'),
                TextEntry::make('operated_by')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('target_type')
            ->columns([
                TextColumn::make('target_type')
                    ->searchable(),
                TextColumn::make('target_id')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('action')
                    ->searchable(),
                TextColumn::make('from_user_id')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('to_user_id')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('reason')
                    ->searchable(),
                TextColumn::make('operated_by')
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
            'index' => ManageCustomerPoolHistories::route('/'),
        ];
    }
}

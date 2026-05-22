<?php

namespace App\Filament\Clusters\CustomerCenter\Resources\CustomerTransferHistories;

use App\Filament\Clusters\CustomerCenter\CustomerCenterCluster;
use App\Filament\Clusters\CustomerCenter\Resources\CustomerTransferHistories\Pages\ManageCustomerTransferHistories;
use App\Filament\Concerns\UsesCrmAccess;
use App\Models\CustomerTransferHistory;
use App\Support\Filament\CrmUi;
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

class CustomerTransferHistoryResource extends Resource
{
    use UsesCrmAccess;

    protected static ?string $model = CustomerTransferHistory::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $navigationLabel = '转移历史';

    protected static ?string $modelLabel = '转移历史';

    protected static ?string $pluralModelLabel = '转移历史';

    protected static ?string $title = '转移历史';

    protected static bool $hasTitleCaseModelLabel = false;

    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $cluster = CustomerCenterCluster::class;

    protected static ?string $recordTitleAttribute = 'target_type';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('target_type')
                    ->options(CrmUi::options('target_type'))
                    ->required(),
                TextInput::make('target_id')
                    ->required()
                    ->numeric(),
                Select::make('from_user_id')
                    ->relationship('fromUser', 'name'),
                Select::make('to_user_id')
                    ->relationship('toUser', 'name'),
                TextInput::make('reason'),
                Select::make('operated_by')
                    ->relationship('operatorUser', 'name')
                    ->required(),
            ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('target_type'),
                TextEntry::make('target_id')
                    ->numeric(),
                TextEntry::make('fromUser.name')
                    ->placeholder('-'),
                TextEntry::make('toUser.name')
                    ->placeholder('-'),
                TextEntry::make('reason')
                    ->placeholder('-'),
                TextEntry::make('operatorUser.name'),
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
                TextColumn::make('fromUser.name')
                    ->searchable(),
                TextColumn::make('toUser.name')
                    ->searchable(),
                TextColumn::make('reason')
                    ->searchable(),
                TextColumn::make('operatorUser.name')
                    ->searchable(),
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
            'index' => ManageCustomerTransferHistories::route('/'),
        ];
    }
}

<?php

namespace App\Filament\Clusters\CustomerCenter\Resources\CustomerPoolHistories;

use App\Filament\Clusters\CustomerCenter\CustomerCenterCluster;
use App\Filament\Clusters\CustomerCenter\Resources\CustomerPoolHistories\Pages\ManageCustomerPoolHistories;
use App\Filament\Concerns\UsesCrmAccess;
use App\Models\CustomerPoolHistory;
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

class CustomerPoolHistoryResource extends Resource
{
    use UsesCrmAccess;

    protected static ?string $model = CustomerPoolHistory::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $navigationLabel = '公海历史';

    protected static ?string $modelLabel = '公海历史';

    protected static ?string $pluralModelLabel = '公海历史';

    protected static ?string $title = '公海历史';

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
                Select::make('action')
                    ->options([
                        'claim' => '领取',
                        'release' => '释放',
                        'transfer' => '转移',
                        'auto_recycle' => '自动回收',
                    ])
                    ->required(),
                Select::make('from_user_id')
                    ->relationship('fromUser', 'name'),
                Select::make('to_user_id')
                    ->relationship('toUser', 'name'),
                TextInput::make('reason'),
                Select::make('operated_by')
                    ->relationship('operatorUser', 'name'),
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
                TextEntry::make('fromUser.name')
                    ->placeholder('-'),
                TextEntry::make('toUser.name')
                    ->placeholder('-'),
                TextEntry::make('reason')
                    ->placeholder('-'),
                TextEntry::make('operatorUser.name')
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
            'index' => ManageCustomerPoolHistories::route('/'),
        ];
    }
}

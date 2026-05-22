<?php

namespace App\Filament\Clusters\OrderFinance\Resources\OrderItems;

use App\Filament\Clusters\OrderFinance\OrderFinanceCluster;
use App\Filament\Clusters\OrderFinance\Resources\OrderItems\Pages\ManageOrderItems;
use App\Filament\Concerns\UsesCrmAccess;
use App\Models\OrderItem;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class OrderItemResource extends Resource
{
    use UsesCrmAccess;

    protected static ?string $model = OrderItem::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $navigationLabel = '订单明细';

    protected static ?string $modelLabel = '订单明细';

    protected static ?string $pluralModelLabel = '订单明细';

    protected static ?string $title = '订单明细';

    protected static bool $hasTitleCaseModelLabel = false;

    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $cluster = OrderFinanceCluster::class;

    protected static ?string $recordTitleAttribute = 'product_name';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('order_id')
                    ->relationship('order', 'order_number')
                    ->required(),
                Select::make('product_id')
                    ->relationship('product', 'name')
                    ->required(),
                Select::make('product_sku_id')
                    ->relationship('sku', 'sku_code'),
                KeyValue::make('specifications')
                    ->columnSpanFull(),
                TextInput::make('quantity')
                    ->required()
                    ->numeric()
                    ->default(1),
                TextInput::make('unit_price')
                    ->required()
                    ->numeric()
                    ->default(0)
                    ->prefix('$'),
                TextInput::make('cost_price')
                    ->required()
                    ->numeric()
                    ->default(0)
                    ->prefix('$'),
                TextInput::make('tax_rate')
                    ->required()
                    ->numeric()
                    ->default(0),
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
                TextEntry::make('order.order_number')
                    ->label('订单'),
                TextEntry::make('product.name'),
                TextEntry::make('sku.sku_code')
                    ->placeholder('-'),
                TextEntry::make('product_name'),
                TextEntry::make('sku_code')
                    ->placeholder('-'),
                TextEntry::make('quantity')
                    ->numeric(),
                TextEntry::make('unit_price')
                    ->money(),
                TextEntry::make('cost_price')
                    ->money(),
                TextEntry::make('tax_rate')
                    ->numeric(),
                TextEntry::make('subtotal_amount')
                    ->numeric(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('product_name')
            ->columns([
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('order.order_number')
                    ->searchable(),
                TextColumn::make('product.name')
                    ->searchable(),
                TextColumn::make('sku.sku_code')
                    ->searchable(),
                TextColumn::make('product_name')
                    ->searchable(),
                TextColumn::make('sku_code')
                    ->searchable(),
                TextColumn::make('quantity')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('unit_price')
                    ->money()
                    ->sortable(),
                TextColumn::make('cost_price')
                    ->money()
                    ->sortable(),
                TextColumn::make('tax_rate')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('subtotal_amount')
                    ->numeric()
                    ->sortable(),
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
            'index' => ManageOrderItems::route('/'),
        ];
    }
}

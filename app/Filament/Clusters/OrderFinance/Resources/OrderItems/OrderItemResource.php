<?php

namespace App\Filament\Clusters\OrderFinance\Resources\OrderItems;

use App\Filament\Clusters\OrderFinance\OrderFinanceCluster;
use App\Filament\Clusters\OrderFinance\Resources\OrderItems\Pages\CreateOrderItem;
use App\Filament\Clusters\OrderFinance\Resources\OrderItems\Pages\EditOrderItem;
use App\Filament\Clusters\OrderFinance\Resources\OrderItems\Pages\ListOrderItems;
use App\Filament\Clusters\OrderFinance\Resources\OrderItems\Pages\ViewOrderItem;
use App\Filament\Clusters\OrderFinance\Resources\OrderItems\Schemas\OrderItemForm;
use App\Filament\Clusters\OrderFinance\Resources\OrderItems\Schemas\OrderItemInfolist;
use App\Filament\Clusters\OrderFinance\Resources\OrderItems\Tables\OrderItemTable;
use App\Filament\Concerns\UsesCrmAccess;
use App\Models\OrderItem;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
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
        return OrderItemForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return OrderItemInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return OrderItemTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListOrderItems::route('/'),
            'create' => CreateOrderItem::route('/create'),
            'view' => ViewOrderItem::route('/{record}'),
            'edit' => EditOrderItem::route('/{record}/edit'),
        ];
    }
}

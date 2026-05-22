<?php

namespace App\Filament\Clusters\OrderFinance\Resources\Orders;

use App\Filament\Clusters\OrderFinance\OrderFinanceCluster;
use App\Filament\Clusters\OrderFinance\Resources\Orders\Pages\CreateOrder;
use App\Filament\Clusters\OrderFinance\Resources\Orders\Pages\EditOrder;
use App\Filament\Clusters\OrderFinance\Resources\Orders\Pages\ListOrders;
use App\Filament\Clusters\OrderFinance\Resources\Orders\Pages\ViewOrder;
use App\Filament\Clusters\OrderFinance\Resources\Orders\Schemas\OrderForm;
use App\Filament\Clusters\OrderFinance\Resources\Orders\Schemas\OrderInfolist;
use App\Filament\Clusters\OrderFinance\Resources\Orders\Tables\OrderTable;
use App\Filament\Concerns\UsesCrmAccess;
use App\Models\Order;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class OrderResource extends Resource
{
    use UsesCrmAccess;

    protected static ?string $model = Order::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $navigationLabel = '订单';

    protected static ?string $modelLabel = '订单';

    protected static ?string $pluralModelLabel = '订单';

    protected static ?string $title = '订单';

    protected static bool $hasTitleCaseModelLabel = false;

    protected static ?string $cluster = OrderFinanceCluster::class;

    protected static ?string $recordTitleAttribute = 'order_number';

    public static function form(Schema $schema): Schema
    {
        return OrderForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return OrderInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return OrderTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListOrders::route('/'),
            'create' => CreateOrder::route('/create'),
            'view' => ViewOrder::route('/{record}'),
            'edit' => EditOrder::route('/{record}/edit'),
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

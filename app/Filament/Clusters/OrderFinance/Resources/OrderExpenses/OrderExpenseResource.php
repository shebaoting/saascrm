<?php

namespace App\Filament\Clusters\OrderFinance\Resources\OrderExpenses;

use App\Filament\Clusters\OrderFinance\OrderFinanceCluster;
use App\Filament\Clusters\OrderFinance\Resources\OrderExpenses\Pages\CreateOrderExpense;
use App\Filament\Clusters\OrderFinance\Resources\OrderExpenses\Pages\EditOrderExpense;
use App\Filament\Clusters\OrderFinance\Resources\OrderExpenses\Pages\ListOrderExpenses;
use App\Filament\Clusters\OrderFinance\Resources\OrderExpenses\Pages\ViewOrderExpense;
use App\Filament\Clusters\OrderFinance\Resources\OrderExpenses\Schemas\OrderExpenseForm;
use App\Filament\Clusters\OrderFinance\Resources\OrderExpenses\Schemas\OrderExpenseInfolist;
use App\Filament\Clusters\OrderFinance\Resources\OrderExpenses\Tables\OrderExpenseTable;
use App\Filament\Concerns\UsesCrmAccess;
use App\Models\OrderExpense;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class OrderExpenseResource extends Resource
{
    use UsesCrmAccess;

    protected static ?string $model = OrderExpense::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $navigationLabel = '支出';

    protected static ?string $modelLabel = '支出';

    protected static ?string $pluralModelLabel = '支出';

    protected static ?string $title = '支出';

    protected static bool $hasTitleCaseModelLabel = false;

    protected static ?string $cluster = OrderFinanceCluster::class;

    protected static ?string $recordTitleAttribute = 'reason';

    public static function form(Schema $schema): Schema
    {
        return OrderExpenseForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return OrderExpenseInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return OrderExpenseTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListOrderExpenses::route('/'),
            'create' => CreateOrderExpense::route('/create'),
            'view' => ViewOrderExpense::route('/{record}'),
            'edit' => EditOrderExpense::route('/{record}/edit'),
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

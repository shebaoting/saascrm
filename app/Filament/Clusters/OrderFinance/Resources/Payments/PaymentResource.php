<?php

namespace App\Filament\Clusters\OrderFinance\Resources\Payments;

use App\Filament\Clusters\OrderFinance\OrderFinanceCluster;
use App\Filament\Clusters\OrderFinance\Resources\Payments\Pages\CreatePayment;
use App\Filament\Clusters\OrderFinance\Resources\Payments\Pages\EditPayment;
use App\Filament\Clusters\OrderFinance\Resources\Payments\Pages\ListPayments;
use App\Filament\Clusters\OrderFinance\Resources\Payments\Pages\ViewPayment;
use App\Filament\Clusters\OrderFinance\Resources\Payments\Schemas\PaymentForm;
use App\Filament\Clusters\OrderFinance\Resources\Payments\Schemas\PaymentInfolist;
use App\Filament\Clusters\OrderFinance\Resources\Payments\Tables\PaymentTable;
use App\Filament\Concerns\UsesCrmAccess;
use App\Models\Payment;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PaymentResource extends Resource
{
    use UsesCrmAccess;

    protected static ?string $model = Payment::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $navigationLabel = '收款';

    protected static ?string $modelLabel = '收款';

    protected static ?string $pluralModelLabel = '收款';

    protected static ?string $title = '收款';

    protected static bool $hasTitleCaseModelLabel = false;

    protected static ?string $cluster = OrderFinanceCluster::class;

    protected static ?string $recordTitleAttribute = 'status';

    public static function form(Schema $schema): Schema
    {
        return PaymentForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return PaymentInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PaymentTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPayments::route('/'),
            'create' => CreatePayment::route('/create'),
            'view' => ViewPayment::route('/{record}'),
            'edit' => EditPayment::route('/{record}/edit'),
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

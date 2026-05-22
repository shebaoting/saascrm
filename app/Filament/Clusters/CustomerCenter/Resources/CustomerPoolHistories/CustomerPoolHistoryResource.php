<?php

namespace App\Filament\Clusters\CustomerCenter\Resources\CustomerPoolHistories;

use App\Filament\Clusters\CustomerCenter\CustomerCenterCluster;
use App\Filament\Clusters\CustomerCenter\Resources\CustomerPoolHistories\Pages\CreateCustomerPoolHistory;
use App\Filament\Clusters\CustomerCenter\Resources\CustomerPoolHistories\Pages\EditCustomerPoolHistory;
use App\Filament\Clusters\CustomerCenter\Resources\CustomerPoolHistories\Pages\ListCustomerPoolHistories;
use App\Filament\Clusters\CustomerCenter\Resources\CustomerPoolHistories\Pages\ViewCustomerPoolHistory;
use App\Filament\Clusters\CustomerCenter\Resources\CustomerPoolHistories\Schemas\CustomerPoolHistoryForm;
use App\Filament\Clusters\CustomerCenter\Resources\CustomerPoolHistories\Schemas\CustomerPoolHistoryInfolist;
use App\Filament\Clusters\CustomerCenter\Resources\CustomerPoolHistories\Tables\CustomerPoolHistoryTable;
use App\Filament\Concerns\UsesCrmAccess;
use App\Models\CustomerPoolHistory;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
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

    public static function canCreate(): bool
    {
        return false;
    }

    public static function form(Schema $schema): Schema
    {
        return CustomerPoolHistoryForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return CustomerPoolHistoryInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CustomerPoolHistoryTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCustomerPoolHistories::route('/'),
            'create' => CreateCustomerPoolHistory::route('/create'),
            'view' => ViewCustomerPoolHistory::route('/{record}'),
            'edit' => EditCustomerPoolHistory::route('/{record}/edit'),
        ];
    }
}

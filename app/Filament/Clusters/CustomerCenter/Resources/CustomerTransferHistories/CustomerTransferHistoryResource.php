<?php

namespace App\Filament\Clusters\CustomerCenter\Resources\CustomerTransferHistories;

use App\Filament\Clusters\CustomerCenter\CustomerCenterCluster;
use App\Filament\Clusters\CustomerCenter\Resources\CustomerTransferHistories\Pages\CreateCustomerTransferHistory;
use App\Filament\Clusters\CustomerCenter\Resources\CustomerTransferHistories\Pages\EditCustomerTransferHistory;
use App\Filament\Clusters\CustomerCenter\Resources\CustomerTransferHistories\Pages\ListCustomerTransferHistories;
use App\Filament\Clusters\CustomerCenter\Resources\CustomerTransferHistories\Pages\ViewCustomerTransferHistory;
use App\Filament\Clusters\CustomerCenter\Resources\CustomerTransferHistories\Schemas\CustomerTransferHistoryForm;
use App\Filament\Clusters\CustomerCenter\Resources\CustomerTransferHistories\Schemas\CustomerTransferHistoryInfolist;
use App\Filament\Clusters\CustomerCenter\Resources\CustomerTransferHistories\Tables\CustomerTransferHistoryTable;
use App\Filament\Concerns\UsesCrmAccess;
use App\Models\CustomerTransferHistory;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
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

    public static function canCreate(): bool
    {
        return false;
    }

    public static function form(Schema $schema): Schema
    {
        return CustomerTransferHistoryForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return CustomerTransferHistoryInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CustomerTransferHistoryTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCustomerTransferHistories::route('/'),
            'create' => CreateCustomerTransferHistory::route('/create'),
            'view' => ViewCustomerTransferHistory::route('/{record}'),
            'edit' => EditCustomerTransferHistory::route('/{record}/edit'),
        ];
    }
}

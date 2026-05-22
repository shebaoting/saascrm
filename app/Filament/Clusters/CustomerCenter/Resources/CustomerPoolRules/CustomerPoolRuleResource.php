<?php

namespace App\Filament\Clusters\CustomerCenter\Resources\CustomerPoolRules;

use App\Filament\Clusters\CustomerCenter\CustomerCenterCluster;
use App\Filament\Clusters\CustomerCenter\Resources\CustomerPoolRules\Pages\CreateCustomerPoolRule;
use App\Filament\Clusters\CustomerCenter\Resources\CustomerPoolRules\Pages\EditCustomerPoolRule;
use App\Filament\Clusters\CustomerCenter\Resources\CustomerPoolRules\Pages\ListCustomerPoolRules;
use App\Filament\Clusters\CustomerCenter\Resources\CustomerPoolRules\Pages\ViewCustomerPoolRule;
use App\Filament\Clusters\CustomerCenter\Resources\CustomerPoolRules\Schemas\CustomerPoolRuleForm;
use App\Filament\Clusters\CustomerCenter\Resources\CustomerPoolRules\Schemas\CustomerPoolRuleInfolist;
use App\Filament\Clusters\CustomerCenter\Resources\CustomerPoolRules\Tables\CustomerPoolRuleTable;
use App\Filament\Concerns\UsesCrmAccess;
use App\Models\CustomerPoolRule;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class CustomerPoolRuleResource extends Resource
{
    use UsesCrmAccess;

    protected static ?string $model = CustomerPoolRule::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $navigationLabel = '公海规则';

    protected static ?string $modelLabel = '公海规则';

    protected static ?string $pluralModelLabel = '公海规则';

    protected static ?string $title = '公海规则';

    protected static bool $hasTitleCaseModelLabel = false;

    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $cluster = CustomerCenterCluster::class;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return CustomerPoolRuleForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return CustomerPoolRuleInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CustomerPoolRuleTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCustomerPoolRules::route('/'),
            'create' => CreateCustomerPoolRule::route('/create'),
            'view' => ViewCustomerPoolRule::route('/{record}'),
            'edit' => EditCustomerPoolRule::route('/{record}/edit'),
        ];
    }
}

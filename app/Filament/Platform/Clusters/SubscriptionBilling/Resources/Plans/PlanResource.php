<?php

namespace App\Filament\Platform\Clusters\SubscriptionBilling\Resources\Plans;

use App\Filament\Platform\Clusters\SubscriptionBilling\Resources\Plans\Pages\CreatePlan;
use App\Filament\Platform\Clusters\SubscriptionBilling\Resources\Plans\Pages\EditPlan;
use App\Filament\Platform\Clusters\SubscriptionBilling\Resources\Plans\Pages\ListPlans;
use App\Filament\Platform\Clusters\SubscriptionBilling\Resources\Plans\Pages\ViewPlan;
use App\Filament\Platform\Clusters\SubscriptionBilling\Resources\Plans\Schemas\PlanForm;
use App\Filament\Platform\Clusters\SubscriptionBilling\Resources\Plans\Schemas\PlanInfolist;
use App\Filament\Platform\Clusters\SubscriptionBilling\Resources\Plans\Tables\PlanTable;
use App\Filament\Platform\Clusters\SubscriptionBilling\SubscriptionBillingCluster;
use App\Models\Plan;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class PlanResource extends Resource
{
    protected static ?string $model = Plan::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $navigationLabel = '套餐';

    protected static ?string $modelLabel = '套餐';

    protected static ?string $pluralModelLabel = '套餐';

    protected static ?string $title = '套餐';

    protected static bool $hasTitleCaseModelLabel = false;

    protected static ?string $cluster = SubscriptionBillingCluster::class;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return PlanForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return PlanInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PlanTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPlans::route('/'),
            'create' => CreatePlan::route('/create'),
            'view' => ViewPlan::route('/{record}'),
            'edit' => EditPlan::route('/{record}/edit'),
        ];
    }
}

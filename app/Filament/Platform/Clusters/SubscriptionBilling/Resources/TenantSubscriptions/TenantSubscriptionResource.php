<?php

namespace App\Filament\Platform\Clusters\SubscriptionBilling\Resources\TenantSubscriptions;

use App\Filament\Platform\Clusters\SubscriptionBilling\Resources\TenantSubscriptions\Pages\CreateTenantSubscription;
use App\Filament\Platform\Clusters\SubscriptionBilling\Resources\TenantSubscriptions\Pages\EditTenantSubscription;
use App\Filament\Platform\Clusters\SubscriptionBilling\Resources\TenantSubscriptions\Pages\ListTenantSubscriptions;
use App\Filament\Platform\Clusters\SubscriptionBilling\Resources\TenantSubscriptions\Pages\ViewTenantSubscription;
use App\Filament\Platform\Clusters\SubscriptionBilling\Resources\TenantSubscriptions\Schemas\TenantSubscriptionForm;
use App\Filament\Platform\Clusters\SubscriptionBilling\Resources\TenantSubscriptions\Schemas\TenantSubscriptionInfolist;
use App\Filament\Platform\Clusters\SubscriptionBilling\Resources\TenantSubscriptions\Tables\TenantSubscriptionTable;
use App\Filament\Platform\Clusters\SubscriptionBilling\SubscriptionBillingCluster;
use App\Models\TenantSubscription;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class TenantSubscriptionResource extends Resource
{
    protected static ?string $model = TenantSubscription::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $navigationLabel = '订阅';

    protected static ?string $modelLabel = '订阅';

    protected static ?string $pluralModelLabel = '订阅';

    protected static ?string $title = '订阅';

    protected static bool $hasTitleCaseModelLabel = false;

    protected static ?string $cluster = SubscriptionBillingCluster::class;

    protected static ?string $recordTitleAttribute = 'status';

    public static function form(Schema $schema): Schema
    {
        return TenantSubscriptionForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return TenantSubscriptionInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TenantSubscriptionTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTenantSubscriptions::route('/'),
            'create' => CreateTenantSubscription::route('/create'),
            'view' => ViewTenantSubscription::route('/{record}'),
            'edit' => EditTenantSubscription::route('/{record}/edit'),
        ];
    }
}

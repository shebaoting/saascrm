<?php

namespace App\Filament\Platform\Clusters\TenantManagement\Resources\Tenants;

use App\Filament\Platform\Clusters\TenantManagement\Resources\Tenants\Pages\CreateTenant;
use App\Filament\Platform\Clusters\TenantManagement\Resources\Tenants\Pages\EditTenant;
use App\Filament\Platform\Clusters\TenantManagement\Resources\Tenants\Pages\ListTenants;
use App\Filament\Platform\Clusters\TenantManagement\Resources\Tenants\Pages\ViewTenant;
use App\Filament\Platform\Clusters\TenantManagement\Resources\Tenants\Schemas\TenantForm;
use App\Filament\Platform\Clusters\TenantManagement\Resources\Tenants\Schemas\TenantInfolist;
use App\Filament\Platform\Clusters\TenantManagement\Resources\Tenants\Tables\TenantTable;
use App\Filament\Platform\Clusters\TenantManagement\TenantManagementCluster;
use App\Models\Tenant;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class TenantResource extends Resource
{
    protected static ?string $model = Tenant::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $navigationLabel = '公司租户';

    protected static ?string $modelLabel = '公司租户';

    protected static ?string $pluralModelLabel = '公司租户';

    protected static ?string $title = '公司租户';

    protected static bool $hasTitleCaseModelLabel = false;

    protected static ?string $cluster = TenantManagementCluster::class;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return TenantForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return TenantInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TenantTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTenants::route('/'),
            'create' => CreateTenant::route('/create'),
            'view' => ViewTenant::route('/{record}'),
            'edit' => EditTenant::route('/{record}/edit'),
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

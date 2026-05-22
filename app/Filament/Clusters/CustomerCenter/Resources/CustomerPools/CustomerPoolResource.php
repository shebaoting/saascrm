<?php

namespace App\Filament\Clusters\CustomerCenter\Resources\CustomerPools;

use App\Filament\Clusters\CustomerCenter\Resources\CustomerPools\Pages\CreateCustomerPool;
use App\Filament\Clusters\CustomerCenter\Resources\CustomerPools\Pages\EditCustomerPool;
use App\Filament\Clusters\CustomerCenter\Resources\CustomerPools\Pages\ListCustomerPools;
use App\Filament\Clusters\CustomerCenter\Resources\CustomerPools\Pages\ViewCustomerPool;
use App\Filament\Clusters\CustomerCenter\Resources\Customers\CustomerResource;
use App\Models\Customer;
use App\Support\CrmAccess;
use Illuminate\Database\Eloquent\Builder;

class CustomerPoolResource extends CustomerResource
{
    protected static ?string $navigationLabel = '客户公海';

    protected static ?string $modelLabel = '客户公海';

    protected static ?string $pluralModelLabel = '客户公海';

    protected static ?string $title = '客户公海';

    protected static ?int $navigationSort = 2;

    protected static ?string $slug = 'customer-pool';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where(function (Builder $query): void {
                $query->whereNull('owner_user_id')
                    ->orWhere('lifecycle_stage', 'pooled');
            });
    }

    public static function getNavigationBadge(): ?string
    {
        $tenantId = CrmAccess::tenantId();

        return (string) Customer::query()
            ->when($tenantId, fn (Builder $query) => $query->where('tenant_id', $tenantId))
            ->where(function (Builder $query): void {
                $query->whereNull('owner_user_id')
                    ->orWhere('lifecycle_stage', 'pooled');
            })
            ->count();
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCustomerPools::route('/'),
            'create' => CreateCustomerPool::route('/create'),
            'view' => ViewCustomerPool::route('/{record}'),
            'edit' => EditCustomerPool::route('/{record}/edit'),
        ];
    }
}

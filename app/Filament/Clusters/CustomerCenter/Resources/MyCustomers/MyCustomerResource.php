<?php

namespace App\Filament\Clusters\CustomerCenter\Resources\MyCustomers;

use App\Filament\Clusters\CustomerCenter\Resources\Customers\CustomerResource;
use App\Filament\Clusters\CustomerCenter\Resources\MyCustomers\Pages\CreateMyCustomer;
use App\Filament\Clusters\CustomerCenter\Resources\MyCustomers\Pages\EditMyCustomer;
use App\Filament\Clusters\CustomerCenter\Resources\MyCustomers\Pages\ListMyCustomers;
use App\Filament\Clusters\CustomerCenter\Resources\MyCustomers\Pages\ViewMyCustomer;
use App\Models\Customer;
use App\Support\CrmAccess;
use Illuminate\Database\Eloquent\Builder;

class MyCustomerResource extends CustomerResource
{
    protected static ?string $navigationLabel = '我的客户';

    protected static ?string $modelLabel = '我的客户';

    protected static ?string $pluralModelLabel = '我的客户';

    protected static ?string $title = '我的客户';

    protected static ?int $navigationSort = 1;

    protected static ?string $slug = 'my-customers';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where(function (Builder $query): void {
                $query->where('owner_user_id', auth()->id())
                    ->orWhereHas('members', fn (Builder $query) => $query->where('users.id', auth()->id()));
            });
    }

    public static function getNavigationBadge(): ?string
    {
        $tenantId = CrmAccess::tenantId();

        return (string) Customer::query()
            ->when($tenantId, fn (Builder $query) => $query->where('tenant_id', $tenantId))
            ->where(function (Builder $query): void {
                $query->where('owner_user_id', auth()->id())
                    ->orWhereHas('members', fn (Builder $query) => $query->where('users.id', auth()->id()));
            })
            ->count();
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMyCustomers::route('/'),
            'create' => CreateMyCustomer::route('/create'),
            'view' => ViewMyCustomer::route('/{record}'),
            'edit' => EditMyCustomer::route('/{record}/edit'),
        ];
    }
}

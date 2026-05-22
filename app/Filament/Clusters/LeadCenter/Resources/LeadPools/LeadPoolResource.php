<?php

namespace App\Filament\Clusters\LeadCenter\Resources\LeadPools;

use App\Filament\Clusters\LeadCenter\Resources\LeadPools\Pages\ManageLeadPools;
use App\Filament\Clusters\LeadCenter\Resources\Leads\LeadResource;
use App\Models\Lead;
use App\Support\CrmAccess;
use Illuminate\Database\Eloquent\Builder;

class LeadPoolResource extends LeadResource
{
    protected static ?string $navigationLabel = '线索池';

    protected static ?string $modelLabel = '线索池';

    protected static ?string $pluralModelLabel = '线索池';

    protected static ?string $title = '线索池';

    protected static ?int $navigationSort = 2;

    protected static ?string $slug = 'lead-pool';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where(function (Builder $query): void {
                $query->whereNull('owner_user_id')
                    ->orWhereIn('status', ['unassigned', 'pooled']);
            });
    }

    public static function getNavigationBadge(): ?string
    {
        $tenantId = CrmAccess::tenantId();

        return (string) Lead::query()
            ->when($tenantId, fn (Builder $query) => $query->where('tenant_id', $tenantId))
            ->where(function (Builder $query): void {
                $query->whereNull('owner_user_id')
                    ->orWhereIn('status', ['unassigned', 'pooled']);
            })
            ->count();
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageLeadPools::route('/'),
        ];
    }
}

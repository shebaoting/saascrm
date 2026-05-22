<?php

namespace App\Filament\Clusters\LeadCenter\Resources\MyLeads;

use App\Filament\Clusters\LeadCenter\Resources\Leads\LeadResource;
use App\Filament\Clusters\LeadCenter\Resources\MyLeads\Pages\CreateMyLead;
use App\Filament\Clusters\LeadCenter\Resources\MyLeads\Pages\EditMyLead;
use App\Filament\Clusters\LeadCenter\Resources\MyLeads\Pages\ListMyLeads;
use App\Filament\Clusters\LeadCenter\Resources\MyLeads\Pages\ViewMyLead;
use App\Models\Lead;
use App\Support\CrmAccess;
use Illuminate\Database\Eloquent\Builder;

class MyLeadResource extends LeadResource
{
    protected static ?string $navigationLabel = '我的线索';

    protected static ?string $modelLabel = '我的线索';

    protected static ?string $pluralModelLabel = '我的线索';

    protected static ?string $title = '我的线索';

    protected static ?int $navigationSort = 1;

    protected static ?string $slug = 'my-leads';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('owner_user_id', auth()->id())
            ->whereNotIn('status', ['converted', 'disqualified']);
    }

    public static function getNavigationBadge(): ?string
    {
        $tenantId = CrmAccess::tenantId();

        return (string) Lead::query()
            ->when($tenantId, fn (Builder $query) => $query->where('tenant_id', $tenantId))
            ->where('owner_user_id', auth()->id())
            ->whereNotIn('status', ['converted', 'disqualified'])
            ->count();
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMyLeads::route('/'),
            'create' => CreateMyLead::route('/create'),
            'view' => ViewMyLead::route('/{record}'),
            'edit' => EditMyLead::route('/{record}/edit'),
        ];
    }
}

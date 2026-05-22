<?php

namespace App\Filament\Clusters\SalesProcess\Resources\SalesTargets;

use App\Filament\Clusters\SalesProcess\Resources\SalesTargets\Pages\CreateSalesTarget;
use App\Filament\Clusters\SalesProcess\Resources\SalesTargets\Pages\EditSalesTarget;
use App\Filament\Clusters\SalesProcess\Resources\SalesTargets\Pages\ListSalesTargets;
use App\Filament\Clusters\SalesProcess\Resources\SalesTargets\Pages\ViewSalesTarget;
use App\Filament\Clusters\SalesProcess\Resources\SalesTargets\Schemas\SalesTargetForm;
use App\Filament\Clusters\SalesProcess\Resources\SalesTargets\Schemas\SalesTargetInfolist;
use App\Filament\Clusters\SalesProcess\Resources\SalesTargets\Tables\SalesTargetTable;
use App\Filament\Clusters\SalesProcess\SalesProcessCluster;
use App\Filament\Concerns\UsesCrmAccess;
use App\Models\Department;
use App\Models\SalesTarget;
use App\Models\User;
use BackedEnum;
use Filament\Facades\Filament;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class SalesTargetResource extends Resource
{
    use UsesCrmAccess;

    protected static ?string $model = SalesTarget::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $navigationLabel = '销售目标';

    protected static ?string $modelLabel = '销售目标';

    protected static ?string $pluralModelLabel = '销售目标';

    protected static ?string $title = '销售目标';

    protected static bool $hasTitleCaseModelLabel = false;

    protected static ?string $cluster = SalesProcessCluster::class;

    protected static ?string $recordTitleAttribute = 'target_type';

    public static function form(Schema $schema): Schema
    {
        return SalesTargetForm::configure($schema, static::class);
    }

    public static function infolist(Schema $schema): Schema
    {
        return SalesTargetInfolist::configure($schema, static::class);
    }

    public static function table(Table $table): Table
    {
        return SalesTargetTable::configure($table, static::class);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSalesTargets::route('/'),
            'create' => CreateSalesTarget::route('/create'),
            'view' => ViewSalesTarget::route('/{record}'),
            'edit' => EditSalesTarget::route('/{record}/edit'),
        ];
    }

    public static function targetTypeOptions(): array
    {
        return [
            'tenant' => '全公司',
            'department' => '部门',
            'user' => '员工',
        ];
    }

    public static function targetOptions(?string $targetType): array
    {
        $tenantId = Filament::getTenant()?->getKey()
            ?? (app()->bound('currentTenant') ? app('currentTenant')?->getKey() : null);

        return match ($targetType) {
            'department' => Department::query()
                ->when($tenantId, fn ($query) => $query->where('tenant_id', $tenantId))
                ->orderBy('name')
                ->pluck('name', 'id')
                ->all(),
            'user' => User::query()
                ->when($tenantId, fn ($query) => $query->whereHas('tenants', fn ($tenantQuery) => $tenantQuery->whereKey($tenantId)))
                ->orderBy('name')
                ->pluck('name', 'id')
                ->all(),
            default => [],
        };
    }

    public static function targetDisplay(SalesTarget $record): string
    {
        if ($record->target_type === 'tenant') {
            return '全公司';
        }

        if (blank($record->target_id)) {
            return '-';
        }

        return match ($record->target_type) {
            'department' => Department::query()
                ->where('tenant_id', $record->tenant_id)
                ->whereKey($record->target_id)
                ->value('name') ?? '#'.$record->target_id,
            'user' => User::query()
                ->whereKey($record->target_id)
                ->value('name') ?? '#'.$record->target_id,
            default => '#'.$record->target_id,
        };
    }
}

<?php

namespace App\Models\Concerns;

use App\Models\Tenant;
use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

trait BelongsToTenant
{
    protected static function bootBelongsToTenant(): void
    {
        static::creating(function (Model $model): void {
            if (! $model->getAttribute('tenant_id') && Filament::getTenant()) {
                $model->setAttribute('tenant_id', Filament::getTenant()->getKey());
            }

            if (static::hasTenantModelColumn($model, 'created_by')) {
                $model->setAttribute('created_by', $model->getAttribute('created_by') ?: Auth::id());
            }

            if (static::hasTenantModelColumn($model, 'updated_by')) {
                $model->setAttribute('updated_by', $model->getAttribute('updated_by') ?: Auth::id());
            }
        });

        static::updating(function (Model $model): void {
            if (static::hasTenantModelColumn($model, 'updated_by')) {
                $model->setAttribute('updated_by', Auth::id());
            }
        });
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function scopeForTenant(Builder $query, int|Tenant $tenant): Builder
    {
        $tenantId = $tenant instanceof Tenant ? $tenant->getKey() : $tenant;

        return $query->where($query->getModel()->getTable().'.tenant_id', $tenantId);
    }

    private static function hasTenantModelColumn(Model $model, string $column): bool
    {
        static $cache = [];

        $key = $model::class.':'.$column;

        return $cache[$key] ??= Schema::hasColumn($model->getTable(), $column);
    }
}

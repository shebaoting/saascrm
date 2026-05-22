<?php

namespace App\Filament\Concerns;

use App\Support\CrmAccess;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

trait UsesCrmAccess
{
    public static function getEloquentQuery(): Builder
    {
        return CrmAccess::scopeQuery(parent::getEloquentQuery(), static::getModel());
    }

    public static function canViewAny(): bool
    {
        return CrmAccess::canForModel(static::getModel(), 'viewAny');
    }

    public static function canCreate(): bool
    {
        return CrmAccess::canForModel(static::getModel(), 'create');
    }

    public static function canView(Model $record): bool
    {
        return CrmAccess::canForRecord($record, 'view');
    }

    public static function canEdit(Model $record): bool
    {
        return CrmAccess::canForRecord($record, 'update');
    }

    public static function canDelete(Model $record): bool
    {
        return CrmAccess::canForRecord($record, 'delete');
    }

    public static function canDeleteAny(): bool
    {
        return CrmAccess::canForModel(static::getModel(), 'delete');
    }

    public static function canForceDelete(Model $record): bool
    {
        return static::canDelete($record);
    }

    public static function canForceDeleteAny(): bool
    {
        return static::canDeleteAny();
    }

    public static function canRestore(Model $record): bool
    {
        return CrmAccess::canForRecord($record, 'update');
    }

    public static function canRestoreAny(): bool
    {
        return CrmAccess::canForModel(static::getModel(), 'update');
    }
}

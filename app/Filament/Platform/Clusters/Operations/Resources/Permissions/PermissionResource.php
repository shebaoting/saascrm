<?php

namespace App\Filament\Platform\Clusters\Operations\Resources\Permissions;

use App\Filament\Platform\Clusters\Operations\OperationsCluster;
use App\Filament\Platform\Clusters\Operations\Resources\Permissions\Pages\CreatePermission;
use App\Filament\Platform\Clusters\Operations\Resources\Permissions\Pages\EditPermission;
use App\Filament\Platform\Clusters\Operations\Resources\Permissions\Pages\ListPermissions;
use App\Filament\Platform\Clusters\Operations\Resources\Permissions\Pages\ViewPermission;
use App\Filament\Platform\Clusters\Operations\Resources\Permissions\Schemas\PermissionForm;
use App\Filament\Platform\Clusters\Operations\Resources\Permissions\Schemas\PermissionInfolist;
use App\Filament\Platform\Clusters\Operations\Resources\Permissions\Tables\PermissionTable;
use App\Models\Permission;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class PermissionResource extends Resource
{
    protected static ?string $model = Permission::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $navigationLabel = '权限';

    protected static ?string $modelLabel = '权限';

    protected static ?string $pluralModelLabel = '权限';

    protected static ?string $title = '权限';

    protected static bool $hasTitleCaseModelLabel = false;

    protected static ?string $cluster = OperationsCluster::class;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return PermissionForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return PermissionInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PermissionTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPermissions::route('/'),
            'create' => CreatePermission::route('/create'),
            'view' => ViewPermission::route('/{record}'),
            'edit' => EditPermission::route('/{record}/edit'),
        ];
    }
}

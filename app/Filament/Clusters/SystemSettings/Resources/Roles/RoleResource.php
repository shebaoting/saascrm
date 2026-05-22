<?php

namespace App\Filament\Clusters\SystemSettings\Resources\Roles;

use App\Filament\Clusters\SystemSettings\Resources\Roles\Pages\CreateRole;
use App\Filament\Clusters\SystemSettings\Resources\Roles\Pages\EditRole;
use App\Filament\Clusters\SystemSettings\Resources\Roles\Pages\ListRoles;
use App\Filament\Clusters\SystemSettings\Resources\Roles\Pages\ViewRole;
use App\Filament\Clusters\SystemSettings\Resources\Roles\Schemas\RoleForm;
use App\Filament\Clusters\SystemSettings\Resources\Roles\Schemas\RoleInfolist;
use App\Filament\Clusters\SystemSettings\Resources\Roles\Tables\RoleTable;
use App\Filament\Clusters\SystemSettings\SystemSettingsCluster;
use App\Filament\Concerns\UsesCrmAccess;
use App\Models\Role;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class RoleResource extends Resource
{
    use UsesCrmAccess;

    protected static ?string $model = Role::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $navigationLabel = '角色权限';

    protected static ?string $modelLabel = '角色权限';

    protected static ?string $pluralModelLabel = '角色权限';

    protected static ?string $title = '角色权限';

    protected static bool $hasTitleCaseModelLabel = false;

    protected static ?string $cluster = SystemSettingsCluster::class;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return RoleForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return RoleInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RoleTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListRoles::route('/'),
            'create' => CreateRole::route('/create'),
            'view' => ViewRole::route('/{record}'),
            'edit' => EditRole::route('/{record}/edit'),
        ];
    }
}

<?php

namespace App\Filament\Platform\Clusters\TenantManagement\Resources\Users;

use App\Filament\Platform\Clusters\TenantManagement\Resources\Users\Pages\CreateUser;
use App\Filament\Platform\Clusters\TenantManagement\Resources\Users\Pages\EditUser;
use App\Filament\Platform\Clusters\TenantManagement\Resources\Users\Pages\ListUsers;
use App\Filament\Platform\Clusters\TenantManagement\Resources\Users\Pages\ViewUser;
use App\Filament\Platform\Clusters\TenantManagement\Resources\Users\Schemas\UserForm;
use App\Filament\Platform\Clusters\TenantManagement\Resources\Users\Schemas\UserInfolist;
use App\Filament\Platform\Clusters\TenantManagement\Resources\Users\Tables\UserTable;
use App\Filament\Platform\Clusters\TenantManagement\TenantManagementCluster;
use App\Models\User;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $navigationLabel = '员工通讯录';

    protected static ?string $modelLabel = '员工通讯录';

    protected static ?string $pluralModelLabel = '员工通讯录';

    protected static ?string $title = '员工通讯录';

    protected static bool $hasTitleCaseModelLabel = false;

    protected static ?string $cluster = TenantManagementCluster::class;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return UserForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return UserInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return UserTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListUsers::route('/'),
            'create' => CreateUser::route('/create'),
            'view' => ViewUser::route('/{record}'),
            'edit' => EditUser::route('/{record}/edit'),
        ];
    }
}

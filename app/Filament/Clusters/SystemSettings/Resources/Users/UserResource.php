<?php

namespace App\Filament\Clusters\SystemSettings\Resources\Users;

use App\Filament\Clusters\SystemSettings\Resources\Users\Pages\CreateUser;
use App\Filament\Clusters\SystemSettings\Resources\Users\Pages\EditUser;
use App\Filament\Clusters\SystemSettings\Resources\Users\Pages\ListUsers;
use App\Filament\Clusters\SystemSettings\Resources\Users\Pages\ViewUser;
use App\Filament\Clusters\SystemSettings\Resources\Users\Schemas\UserForm;
use App\Filament\Clusters\SystemSettings\Resources\Users\Schemas\UserInfolist;
use App\Filament\Clusters\SystemSettings\Resources\Users\Tables\UserTable;
use App\Filament\Clusters\SystemSettings\SystemSettingsCluster;
use App\Filament\Concerns\UsesCrmAccess;
use App\Models\User;
use BackedEnum;
use Filament\Facades\Filament;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class UserResource extends Resource
{
    use UsesCrmAccess;

    protected static ?string $model = User::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $navigationLabel = '员工通讯录';

    protected static ?string $modelLabel = '员工通讯录';

    protected static ?string $pluralModelLabel = '员工通讯录';

    protected static ?string $title = '员工通讯录';

    protected static bool $hasTitleCaseModelLabel = false;

    protected static ?string $cluster = SystemSettingsCluster::class;

    protected static ?string $recordTitleAttribute = 'name';

    protected static bool $isScopedToTenant = false;

    public static function getEloquentQuery(): Builder
    {
        $tenant = Filament::getTenant();

        return parent::getEloquentQuery()
            ->when($tenant, fn (Builder $query) => $query->whereHas('tenants', fn (Builder $query) => $query->whereKey($tenant->getKey())));
    }

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

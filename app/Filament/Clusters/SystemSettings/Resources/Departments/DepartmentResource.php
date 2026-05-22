<?php

namespace App\Filament\Clusters\SystemSettings\Resources\Departments;

use App\Filament\Clusters\SystemSettings\Resources\Departments\Pages\CreateDepartment;
use App\Filament\Clusters\SystemSettings\Resources\Departments\Pages\EditDepartment;
use App\Filament\Clusters\SystemSettings\Resources\Departments\Pages\ListDepartments;
use App\Filament\Clusters\SystemSettings\Resources\Departments\Pages\ViewDepartment;
use App\Filament\Clusters\SystemSettings\Resources\Departments\Schemas\DepartmentForm;
use App\Filament\Clusters\SystemSettings\Resources\Departments\Schemas\DepartmentInfolist;
use App\Filament\Clusters\SystemSettings\Resources\Departments\Tables\DepartmentTable;
use App\Filament\Clusters\SystemSettings\SystemSettingsCluster;
use App\Filament\Concerns\UsesCrmAccess;
use App\Models\Department;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class DepartmentResource extends Resource
{
    use UsesCrmAccess;

    protected static ?string $model = Department::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $navigationLabel = '部门';

    protected static ?string $modelLabel = '部门';

    protected static ?string $pluralModelLabel = '部门';

    protected static ?string $title = '部门';

    protected static bool $hasTitleCaseModelLabel = false;

    protected static ?string $cluster = SystemSettingsCluster::class;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return DepartmentForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return DepartmentInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DepartmentTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListDepartments::route('/'),
            'create' => CreateDepartment::route('/create'),
            'view' => ViewDepartment::route('/{record}'),
            'edit' => EditDepartment::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}

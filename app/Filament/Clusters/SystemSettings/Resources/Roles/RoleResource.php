<?php

namespace App\Filament\Clusters\SystemSettings\Resources\Roles;

use App\Filament\Clusters\SystemSettings\Resources\Roles\Pages\ManageRoles;
use App\Filament\Clusters\SystemSettings\SystemSettingsCluster;
use App\Filament\Concerns\UsesCrmAccess;
use App\Models\Department;
use App\Models\Role;
use App\Models\User;
use App\Support\CrmAccess;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
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
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                TextInput::make('guard_name')
                    ->required()
                    ->default('web'),
                Select::make('data_scope')
                    ->label('数据范围')
                    ->options([
                        'self' => '仅本人',
                        'department' => '本部门',
                        'department_tree' => '本部门及下级',
                        'all' => '全部数据',
                        'custom' => '自定义',
                    ])
                    ->live()
                    ->required()
                    ->default('all'),
                Select::make('custom_department_ids')
                    ->label('自定义部门')
                    ->multiple()
                    ->options(fn (): array => Department::query()
                        ->where('tenant_id', CrmAccess::tenantId())
                        ->orderBy('sort_order')
                        ->orderBy('name')
                        ->pluck('name', 'id')
                        ->all())
                    ->visible(fn (Get $get): bool => $get('data_scope') === 'custom'),
                Select::make('custom_user_ids')
                    ->label('自定义人员')
                    ->multiple()
                    ->options(fn (): array => User::query()
                        ->whereHas('tenants', fn ($query) => $query->whereKey(CrmAccess::tenantId()))
                        ->orderBy('name')
                        ->pluck('name', 'id')
                        ->all())
                    ->visible(fn (Get $get): bool => $get('data_scope') === 'custom'),
                CheckboxList::make('permissions')
                    ->relationship('permissions', 'label')
                    ->columns(2)
                    ->columnSpanFull(),
            ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('tenant.name')
                    ->label('租户')
                    ->placeholder('-'),
                TextEntry::make('name'),
                TextEntry::make('guard_name'),
                TextEntry::make('data_scope'),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('guard_name')
                    ->searchable(),
                TextColumn::make('data_scope')
                    ->searchable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageRoles::route('/'),
        ];
    }
}

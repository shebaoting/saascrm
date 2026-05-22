<?php

namespace App\Filament\Clusters\SystemSettings\Resources\Roles\Schemas;

use App\Models\Department;
use App\Models\User;
use App\Support\CrmAccess;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class RoleForm
{
    public static function configure(Schema $schema): Schema
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
}

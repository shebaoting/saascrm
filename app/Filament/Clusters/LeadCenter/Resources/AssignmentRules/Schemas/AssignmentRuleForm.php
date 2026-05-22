<?php

namespace App\Filament\Clusters\LeadCenter\Resources\AssignmentRules\Schemas;

use App\Filament\Clusters\LeadCenter\Resources\LeadScoreRules\LeadScoreRuleResource;
use App\Models\Department;
use App\Models\User;
use App\Support\CrmAccess;
use App\Support\Filament\CrmUi;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class AssignmentRuleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                Select::make('target_type')
                    ->options(CrmUi::options('target_type'))
                    ->required()
                    ->default('lead'),
                Select::make('method')
                    ->options(CrmUi::options('assignment.method'))
                    ->required()
                    ->default('round_robin'),
                Select::make('department_id')
                    ->options(fn (): array => Department::query()
                        ->where('tenant_id', CrmAccess::tenantId())
                        ->orderBy('sort_order')
                        ->orderBy('name')
                        ->pluck('name', 'id')
                        ->all()),
                Select::make('user_ids')
                    ->multiple()
                    ->options(fn (): array => User::query()
                        ->whereHas('tenants', fn ($query) => $query->whereKey(CrmAccess::tenantId()))
                        ->orderBy('name')
                        ->pluck('name', 'id')
                        ->all()),
                TextInput::make('max_per_user_daily')
                    ->numeric(),
                Repeater::make('conditions')
                    ->relationship('conditions')
                    ->schema([
                        Select::make('field')
                            ->options(LeadScoreRuleResource::fieldOptions())
                            ->searchable()
                            ->required(),
                        Select::make('operator')
                            ->options(LeadScoreRuleResource::operatorOptions())
                            ->required(),
                        TagsInput::make('value')
                            ->separator(',')
                            ->placeholder('可填多个值'),
                    ])
                    ->columns(3)
                    ->columnSpanFull()
                    ->addActionLabel('添加条件'),
                TextInput::make('priority')
                    ->required()
                    ->numeric()
                    ->default(0),
                Toggle::make('is_active')
                    ->required(),
            ]);
    }
}

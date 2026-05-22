<?php

namespace App\Filament\Clusters\SystemSettings\Resources\AutomationRules\Schemas;

use App\Filament\Clusters\SystemSettings\Resources\AutomationRules\AutomationRuleResource;
use App\Models\User;
use App\Support\CrmAccess;
use App\Support\Filament\CrmUi;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class AutomationRuleForm
{
    public static function configure(Schema $schema, string $resourceClass = AutomationRuleResource::class): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                Select::make('trigger_type')
                    ->options(CrmUi::options('automation.trigger_type'))
                    ->required(),
                Select::make('target_type')
                    ->options(CrmUi::options('target_type'))
                    ->required(),
                Repeater::make('conditions')
                    ->label('条件')
                    ->schema([
                        Select::make('field')
                            ->label('字段')
                            ->options($resourceClass::conditionFieldOptions())
                            ->required(),
                        Select::make('operator')
                            ->label('条件')
                            ->options($resourceClass::operatorOptions())
                            ->default('=')
                            ->required(),
                        TextInput::make('value')
                            ->label('值'),
                    ])
                    ->columns(3)
                    ->columnSpanFull()
                    ->addActionLabel('添加条件'),
                Repeater::make('actions')
                    ->label('动作')
                    ->relationship('actions')
                    ->schema([
                        Select::make('action_type')
                            ->options(CrmUi::options('automation.action_type'))
                            ->required(),
                        Select::make('payload.assignee_id')
                            ->label('负责人')
                            ->options(fn (): array => User::query()
                                ->whereHas('tenants', fn ($query) => $query->whereKey(CrmAccess::tenantId()))
                                ->orderBy('name')
                                ->pluck('name', 'id')
                                ->all())
                            ->visible(fn ($get): bool => in_array($get('action_type'), ['create_task', 'assign_owner'], true)),
                        Select::make('payload.user_id')
                            ->label('通知对象')
                            ->options(fn (): array => User::query()
                                ->whereHas('tenants', fn ($query) => $query->whereKey(CrmAccess::tenantId()))
                                ->orderBy('name')
                                ->pluck('name', 'id')
                                ->all())
                            ->visible(fn ($get): bool => $get('action_type') === 'send_notification'),
                        TextInput::make('payload.title')
                            ->label('标题')
                            ->visible(fn ($get): bool => in_array($get('action_type'), ['create_task', 'send_notification'], true)),
                        TextInput::make('payload.body')
                            ->label('通知内容')
                            ->visible(fn ($get): bool => $get('action_type') === 'send_notification'),
                        TextInput::make('payload.due_days')
                            ->label('截止天数')
                            ->numeric()
                            ->visible(fn ($get): bool => $get('action_type') === 'create_task'),
                        TextInput::make('payload.reason')
                            ->label('原因')
                            ->visible(fn ($get): bool => $get('action_type') === 'move_to_pool'),
                        TextInput::make('sort_order')
                            ->numeric()
                            ->default(0),
                    ])
                    ->columns(3)
                    ->columnSpanFull()
                    ->addActionLabel('添加动作'),
                Toggle::make('is_active')
                    ->default(true)
                    ->required(),
            ]);
    }
}

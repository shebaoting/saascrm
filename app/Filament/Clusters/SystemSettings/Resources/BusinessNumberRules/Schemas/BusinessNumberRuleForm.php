<?php

namespace App\Filament\Clusters\SystemSettings\Resources\BusinessNumberRules\Schemas;

use App\Services\Crm\BusinessNumberService;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class BusinessNumberRuleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('module')
                ->label('业务对象')
                ->options(BusinessNumberService::moduleLabels())
                ->required(),
            TextInput::make('name')
                ->label('规则名称'),
            TextInput::make('prefix')
                ->label('前缀')
                ->maxLength(50),
            TextInput::make('pattern')
                ->label('规则模板')
                ->helperText('可用变量：{PREFIX} {YYYY} {YY} {MM} {DD} {SEQ} {SUFFIX}')
                ->required()
                ->default('{PREFIX}{YYYY}{MM}{DD}{SEQ}'),
            TextInput::make('suffix')
                ->label('后缀')
                ->maxLength(50),
            TextInput::make('sequence_length')
                ->label('流水号位数')
                ->numeric()
                ->required()
                ->default(4),
            Select::make('reset_period')
                ->label('重置周期')
                ->options([
                    'daily' => '每天',
                    'monthly' => '每月',
                    'yearly' => '每年',
                    'never' => '永不',
                ])
                ->required()
                ->default('daily'),
            TextInput::make('current_sequence')
                ->label('当前流水')
                ->numeric()
                ->default(0),
            Toggle::make('is_active')
                ->label('启用')
                ->default(true),
        ]);
    }
}

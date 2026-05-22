<?php

namespace App\Filament\Clusters\SystemSettings\Resources\BusinessNumberRules;

use App\Filament\Clusters\SystemSettings\Resources\BusinessNumberRules\Pages\ManageBusinessNumberRules;
use App\Filament\Clusters\SystemSettings\SystemSettingsCluster;
use App\Filament\Concerns\UsesCrmAccess;
use App\Models\BusinessNumberRule;
use App\Services\Crm\BusinessNumberService;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class BusinessNumberRuleResource extends Resource
{
    use UsesCrmAccess;

    protected static ?string $model = BusinessNumberRule::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedHashtag;

    protected static ?string $navigationLabel = '编号规则';

    protected static ?string $modelLabel = '编号规则';

    protected static ?string $pluralModelLabel = '编号规则';

    protected static ?string $title = '编号规则';

    protected static bool $hasTitleCaseModelLabel = false;

    protected static ?string $cluster = SystemSettingsCluster::class;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
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

    public static function infolist(Schema $schema): Schema
    {
        return $schema->components([
            TextEntry::make('module')
                ->formatStateUsing(fn (string $state): string => BusinessNumberService::moduleLabels()[$state] ?? $state),
            TextEntry::make('pattern'),
            TextEntry::make('current_sequence')
                ->numeric(),
            TextEntry::make('last_sequence_key')
                ->placeholder('-'),
            IconEntry::make('is_active')
                ->boolean(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('module')
                    ->label('业务对象')
                    ->formatStateUsing(fn (string $state): string => BusinessNumberService::moduleLabels()[$state] ?? $state)
                    ->searchable(),
                TextColumn::make('name')
                    ->label('规则名称')
                    ->searchable(),
                TextColumn::make('pattern')
                    ->label('模板')
                    ->searchable(),
                TextColumn::make('current_sequence')
                    ->label('当前流水')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('reset_period')
                    ->label('重置周期')
                    ->sortable(),
                IconColumn::make('is_active')
                    ->label('启用')
                    ->boolean(),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
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
            'index' => ManageBusinessNumberRules::route('/'),
        ];
    }
}

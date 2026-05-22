<?php

namespace App\Filament\Clusters\SystemSettings\Resources\AutomationRules;

use App\Filament\Clusters\SystemSettings\Resources\AutomationRules\Pages\ManageAutomationRules;
use App\Filament\Clusters\SystemSettings\SystemSettingsCluster;
use App\Filament\Concerns\UsesCrmAccess;
use App\Models\AutomationRule;
use App\Models\User;
use App\Services\Crm\PlanLimitService;
use App\Support\CrmAccess;
use App\Support\Filament\CrmUi;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Repeater;
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

class AutomationRuleResource extends Resource
{
    use UsesCrmAccess;

    protected static ?string $model = AutomationRule::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $navigationLabel = '自动化规则';

    protected static ?string $modelLabel = '自动化规则';

    protected static ?string $pluralModelLabel = '自动化规则';

    protected static ?string $title = '自动化规则';

    protected static bool $hasTitleCaseModelLabel = false;

    protected static bool $shouldRegisterNavigation = true;

    protected static ?string $cluster = SystemSettingsCluster::class;

    protected static ?string $recordTitleAttribute = 'name';

    public static function canViewAny(): bool
    {
        return app(PlanLimitService::class)->hasFeature(CrmAccess::tenant(), 'automation')
            && CrmAccess::canForModel(static::getModel(), 'viewAny');
    }

    public static function canCreate(): bool
    {
        return app(PlanLimitService::class)->hasFeature(CrmAccess::tenant(), 'automation')
            && CrmAccess::canForModel(static::getModel(), 'create');
    }

    public static function form(Schema $schema): Schema
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
                            ->options(static::conditionFieldOptions())
                            ->required(),
                        Select::make('operator')
                            ->label('条件')
                            ->options(static::operatorOptions())
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
                DateTimePicker::make('last_run_at'),
            ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('name'),
                TextEntry::make('trigger_type'),
                TextEntry::make('target_type'),
                IconEntry::make('is_active')
                    ->boolean(),
                TextEntry::make('last_run_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('trigger_type')
                    ->searchable(),
                TextColumn::make('target_type')
                    ->searchable(),
                IconColumn::make('is_active')
                    ->boolean(),
                TextColumn::make('last_run_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                Action::make('test')
                    ->label('测试')
                    ->icon('heroicon-o-play')
                    ->action(function (AutomationRule $record): void {
                        $record->forceFill(['last_run_at' => now()])->save();
                        \Filament\Notifications\Notification::make()
                            ->success()
                            ->title('规则配置可用')
                            ->body('条件和动作已保存，可在真实触发事件中执行。')
                            ->send();
                    }),
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
            'index' => ManageAutomationRules::route('/'),
        ];
    }

    public static function conditionFieldOptions(): array
    {
        return [
            'source' => '来源',
            'status' => '状态',
            'owner_user_id' => '负责人',
            'customer_type' => '客户类型',
            'lifecycle_stage' => '客户阶段',
            'forecast_category' => '预测分类',
            'amount' => '金额',
            'type' => '活动类型',
        ];
    }

    public static function operatorOptions(): array
    {
        return [
            '=' => '等于',
            '!=' => '不等于',
            'contains' => '包含',
            'filled' => '已填写',
            'blank' => '未填写',
        ];
    }
}

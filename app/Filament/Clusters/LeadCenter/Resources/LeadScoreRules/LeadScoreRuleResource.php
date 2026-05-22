<?php

namespace App\Filament\Clusters\LeadCenter\Resources\LeadScoreRules;

use App\Filament\Clusters\LeadCenter\LeadCenterCluster;
use App\Filament\Clusters\LeadCenter\Resources\LeadScoreRules\Pages\ManageLeadScoreRules;
use App\Filament\Concerns\UsesCrmAccess;
use App\Models\LeadScoreRule;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
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

class LeadScoreRuleResource extends Resource
{
    use UsesCrmAccess;

    protected static ?string $model = LeadScoreRule::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $navigationLabel = '线索评分规则';

    protected static ?string $modelLabel = '线索评分规则';

    protected static ?string $pluralModelLabel = '线索评分规则';

    protected static ?string $title = '线索评分规则';

    protected static bool $hasTitleCaseModelLabel = false;

    protected static bool $shouldRegisterNavigation = true;

    protected static ?string $cluster = LeadCenterCluster::class;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                Select::make('field')
                    ->options(static::fieldOptions())
                    ->searchable()
                    ->required(),
                Select::make('operator')
                    ->options(static::operatorOptions())
                    ->required(),
                TagsInput::make('value')
                    ->separator(',')
                    ->placeholder('可填多个值'),
                TextInput::make('score')
                    ->required()
                    ->numeric()
                    ->default(0),
                Toggle::make('is_active')
                    ->default(true)
                    ->required(),
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
                TextEntry::make('field'),
                TextEntry::make('operator'),
                TextEntry::make('value')
                    ->formatStateUsing(fn ($state): string => implode('、', \Illuminate\Support\Arr::wrap($state))),
                TextEntry::make('score')
                    ->numeric(),
                IconEntry::make('is_active')
                    ->boolean(),
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
                TextColumn::make('field')
                    ->searchable(),
                TextColumn::make('operator')
                    ->searchable(),
                TextColumn::make('value')
                    ->formatStateUsing(fn ($state): string => implode('、', \Illuminate\Support\Arr::wrap($state)))
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('score')
                    ->numeric()
                    ->sortable(),
                IconColumn::make('is_active')
                    ->boolean(),
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
            'index' => ManageLeadScoreRules::route('/'),
        ];
    }

    public static function fieldOptions(): array
    {
        return [
            'source' => '来源',
            'area_id' => '地区',
            'industry' => '行业',
            'company_name' => '公司名称',
            'contact_name' => '联系人',
            'phone' => '手机号',
            'email' => '邮箱',
            'score' => '当前分数',
            'status' => '状态',
            'custom_fields.industry' => '自定义字段：行业',
            'custom_fields.product_line' => '自定义字段：产品线',
            'behavior.activity_count' => '行为：跟进次数',
            'behavior.has_follow_up' => '行为：有下次跟进',
            'behavior.days_since_last_activity' => '行为：距上次跟进天数',
            'completeness.percent' => '资料完整度',
        ];
    }

    public static function operatorOptions(): array
    {
        return [
            'eq' => '等于',
            'neq' => '不等于',
            'contains' => '包含',
            'in' => '属于任一',
            'not_in' => '不属于',
            'not_empty' => '已填写',
            'empty' => '未填写',
            'gt' => '大于',
            'gte' => '大于等于',
            'lt' => '小于',
            'lte' => '小于等于',
            'between' => '介于两值之间',
        ];
    }
}

<?php

namespace App\Filament\Clusters\SalesProcess\Resources\SalesTargets;

use App\Filament\Clusters\SalesProcess\Resources\SalesTargets\Pages\ManageSalesTargets;
use App\Filament\Clusters\SalesProcess\SalesProcessCluster;
use App\Filament\Concerns\UsesCrmAccess;
use App\Models\Department;
use App\Models\SalesTarget;
use App\Models\User;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Facades\Filament;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SalesTargetResource extends Resource
{
    use UsesCrmAccess;

    protected static ?string $model = SalesTarget::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $navigationLabel = '销售目标';

    protected static ?string $modelLabel = '销售目标';

    protected static ?string $pluralModelLabel = '销售目标';

    protected static ?string $title = '销售目标';

    protected static bool $hasTitleCaseModelLabel = false;

    protected static ?string $cluster = SalesProcessCluster::class;

    protected static ?string $recordTitleAttribute = 'target_type';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('target_type')
                    ->options(static::targetTypeOptions())
                    ->required()
                    ->default('user')
                    ->live()
                    ->afterStateUpdated(fn ($state, callable $set) => $set('target_id', null)),
                Select::make('target_id')
                    ->label('目标对象')
                    ->options(fn (Get $get): array => static::targetOptions($get('target_type')))
                    ->visible(fn (Get $get): bool => $get('target_type') !== 'tenant')
                    ->required(fn (Get $get): bool => $get('target_type') !== 'tenant'),
                Select::make('period_type')
                    ->options([
                        'week' => '周',
                        'month' => '月',
                        'quarter' => '季度',
                        'year' => '年',
                    ])
                    ->required()
                    ->default('month'),
                DatePicker::make('period_start')
                    ->required(),
                DatePicker::make('period_end')
                    ->required(),
                TextInput::make('target_amount')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('target_payment_amount')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('target_customer_count')
                    ->required()
                    ->numeric()
                    ->default(0),
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
                TextEntry::make('target_type'),
                TextEntry::make('target_id')
                    ->label('目标对象')
                    ->formatStateUsing(fn ($state, SalesTarget $record): string => static::targetDisplay($record))
                    ->placeholder('-'),
                TextEntry::make('period_type'),
                TextEntry::make('period_start')
                    ->date(),
                TextEntry::make('period_end')
                    ->date(),
                TextEntry::make('target_amount')
                    ->numeric(),
                TextEntry::make('target_payment_amount')
                    ->numeric(),
                TextEntry::make('target_customer_count')
                    ->numeric(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('target_type')
            ->columns([
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('target_type')
                    ->searchable(),
                TextColumn::make('target_id')
                    ->label('目标对象')
                    ->formatStateUsing(fn ($state, SalesTarget $record): string => static::targetDisplay($record))
                    ->sortable(),
                TextColumn::make('period_type')
                    ->searchable(),
                TextColumn::make('period_start')
                    ->date()
                    ->sortable(),
                TextColumn::make('period_end')
                    ->date()
                    ->sortable(),
                TextColumn::make('target_amount')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('target_payment_amount')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('target_customer_count')
                    ->numeric()
                    ->sortable(),
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
            'index' => ManageSalesTargets::route('/'),
        ];
    }

    private static function targetTypeOptions(): array
    {
        return [
            'tenant' => '全公司',
            'department' => '部门',
            'user' => '员工',
        ];
    }

    private static function targetOptions(?string $targetType): array
    {
        $tenantId = Filament::getTenant()?->getKey()
            ?? (app()->bound('currentTenant') ? app('currentTenant')?->getKey() : null);

        return match ($targetType) {
            'department' => Department::query()
                ->when($tenantId, fn ($query) => $query->where('tenant_id', $tenantId))
                ->orderBy('name')
                ->pluck('name', 'id')
                ->all(),
            'user' => User::query()
                ->when($tenantId, fn ($query) => $query->whereHas('tenants', fn ($tenantQuery) => $tenantQuery->whereKey($tenantId)))
                ->orderBy('name')
                ->pluck('name', 'id')
                ->all(),
            default => [],
        };
    }

    private static function targetDisplay(SalesTarget $record): string
    {
        if ($record->target_type === 'tenant') {
            return '全公司';
        }

        if (blank($record->target_id)) {
            return '-';
        }

        return match ($record->target_type) {
            'department' => Department::query()
                ->where('tenant_id', $record->tenant_id)
                ->whereKey($record->target_id)
                ->value('name') ?? '#'.$record->target_id,
            'user' => User::query()
                ->whereKey($record->target_id)
                ->value('name') ?? '#'.$record->target_id,
            default => '#'.$record->target_id,
        };
    }
}

<?php

namespace App\Filament\Clusters\CustomerCenter\Resources\Customers;

use App\Filament\Clusters\CustomerCenter\CustomerCenterCluster;
use App\Filament\Clusters\CustomerCenter\Resources\Customers\Pages\CustomerProfile;
use App\Filament\Clusters\CustomerCenter\Resources\Customers\Pages\ManageCustomers;
use App\Filament\Concerns\UsesCrmAccess;
use App\Models\Customer;
use App\Services\Crm\CustomerMergeService;
use App\Services\Crm\CustomerPoolService;
use App\Support\CrmAccess;
use App\Support\Filament\CustomFieldUi;
use App\Support\Filament\CrmUi;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CustomerResource extends Resource
{
    use UsesCrmAccess;

    protected static ?string $model = Customer::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $navigationLabel = '全部客户';

    protected static ?string $modelLabel = '全部客户';

    protected static ?string $pluralModelLabel = '全部客户';

    protected static ?string $title = '全部客户';

    protected static bool $hasTitleCaseModelLabel = false;

    protected static ?string $cluster = CustomerCenterCluster::class;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('基础信息')
                    ->schema([
                        CustomFieldUi::applyLayout(TextInput::make('name')->required(), 'customer', 'name'),
                        CustomFieldUi::applyLayout(TextInput::make('short_name'), 'customer', 'short_name'),
                        CustomFieldUi::applyLayout(Select::make('customer_type')->options(CrmUi::options('customer.customer_type'))->required()->default('company'), 'customer', 'customer_type'),
                        CustomFieldUi::applyLayout(Select::make('lifecycle_stage')->options(CrmUi::options('customer.lifecycle_stage'))->required()->default('new'), 'customer', 'lifecycle_stage'),
                        CustomFieldUi::applyLayout(TextInput::make('phone')->tel(), 'customer', 'phone'),
                        CustomFieldUi::applyLayout(TextInput::make('email')->label('邮箱')->email(), 'customer', 'email'),
                        CustomFieldUi::applyLayout(TextInput::make('website')->url(), 'customer', 'website'),
                        CustomFieldUi::applyLayout(TextInput::make('registered_address')->columnSpanFull(), 'customer', 'registered_address'),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),
                Section::make('归属信息')
                    ->schema([
                        CustomFieldUi::applyLayout(Select::make('owner_user_id')->relationship('owner', 'name'), 'customer', 'owner_user_id'),
                        CustomFieldUi::applyLayout(TextInput::make('source'), 'customer', 'source'),
                        CustomFieldUi::applyLayout(TextInput::make('country_code'), 'customer', 'country_code'),
                        CustomFieldUi::applyLayout(TextInput::make('area_id'), 'customer', 'area_id'),
                        CustomFieldUi::applyLayout(TagsInput::make('tags')->columnSpanFull(), 'customer', 'tags'),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),
                Section::make('财务信息')
                    ->schema([
                        CustomFieldUi::applyLayout(TextInput::make('industry'), 'customer', 'industry'),
                        CustomFieldUi::applyLayout(TextInput::make('company_size'), 'customer', 'company_size'),
                        CustomFieldUi::applyLayout(TextInput::make('annual_revenue')->numeric(), 'customer', 'annual_revenue'),
                    ])
                    ->columns(3)
                    ->columnSpanFull(),
                ...CustomFieldUi::formSections('customer'),
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
                TextEntry::make('deleted_at')
                    ->dateTime()
                    ->visible(fn (Customer $record): bool => $record->trashed()),
                TextEntry::make('customer_number')
                    ->label('客户编号')
                    ->placeholder('-'),
                TextEntry::make('name'),
                TextEntry::make('short_name')
                    ->placeholder('-'),
                TextEntry::make('customer_type'),
                TextEntry::make('lifecycle_stage'),
                TextEntry::make('owner.name')
                    ->placeholder('-'),
                TextEntry::make('source')
                    ->placeholder('-'),
                TextEntry::make('country_code')
                    ->placeholder('-'),
                TextEntry::make('area_id')
                    ->placeholder('-'),
                TextEntry::make('email')
                    ->label('邮箱')
                    ->placeholder('-'),
                TextEntry::make('phone')
                    ->placeholder('-'),
                TextEntry::make('registered_address')
                    ->placeholder('-'),
                TextEntry::make('website')
                    ->placeholder('-'),
                TextEntry::make('industry')
                    ->placeholder('-'),
                TextEntry::make('company_size')
                    ->placeholder('-'),
                TextEntry::make('annual_revenue')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('pool_entered_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('last_activity_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('next_activity_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('first_order_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('last_order_at')
                    ->dateTime()
                    ->placeholder('-'),
                ...CustomFieldUi::infolistSections('customer'),
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
                TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('customer_number')
                    ->label('客户编号')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('short_name')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('customer_type')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('lifecycle_stage')
                    ->searchable(),
                TextColumn::make('owner.name')
                    ->searchable(),
                TextColumn::make('source')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('country_code')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('area_id')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('email')
                    ->label('邮箱')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('phone')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('registered_address')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('website')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('industry')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('company_size')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('annual_revenue')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('pool_entered_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('last_activity_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('next_activity_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('first_order_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('last_order_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                ...CustomFieldUi::tableColumns('customer'),
            ])
            ->filters([
                SelectFilter::make('lifecycle_stage')
                    ->options(CrmUi::options('customer.lifecycle_stage')),
                SelectFilter::make('customer_type')
                    ->options(CrmUi::options('customer.customer_type')),
                SelectFilter::make('owner_user_id')
                    ->relationship('owner', 'name'),
                TrashedFilter::make(),
                ...CustomFieldUi::tableFilters('customer'),
            ])
            ->recordActions([
                Action::make('profile')
                    ->label('客户360')
                    ->icon('heroicon-o-identification')
                    ->url(fn (Customer $record): string => static::getUrl('profile', ['record' => $record])),
                Action::make('claim')
                    ->label('领取')
                    ->icon('heroicon-o-hand-raised')
                    ->visible(fn (Customer $record): bool => CrmAccess::hasPermission('customer.claim') && (blank($record->owner_user_id) || $record->lifecycle_stage === 'pooled'))
                    ->action(function (Customer $record): void {
                        app(CustomerPoolService::class)->claimCustomer($record, auth()->user());

                        Notification::make()->success()->title('客户已领取')->send();
                    }),
                Action::make('release')
                    ->label('释放到公海')
                    ->icon('heroicon-o-archive-box-arrow-down')
                    ->color('gray')
                    ->visible(fn (Customer $record): bool => CrmAccess::hasPermission('customer.recycle') && filled($record->owner_user_id))
                    ->form([
                        TextInput::make('reason')
                            ->label('释放原因')
                            ->maxLength(255),
                    ])
                    ->action(function (Customer $record, array $data): void {
                        app(CustomerPoolService::class)->releaseCustomer($record, $data['reason'] ?? '手动释放');

                        Notification::make()->success()->title('客户已进入公海')->send();
                    }),
                Action::make('merge')
                    ->label('合并客户')
                    ->icon('heroicon-o-arrows-right-left')
                    ->color('warning')
                    ->visible(fn (): bool => CrmAccess::hasPermission('customer.merge'))
                    ->form([
                        Select::make('target_customer_id')
                            ->label('合并到')
                            ->options(fn (Customer $record): array => Customer::query()
                                ->where('tenant_id', $record->tenant_id)
                                ->whereKeyNot($record->getKey())
                                ->orderBy('name')
                                ->pluck('name', 'id')
                                ->all())
                            ->searchable()
                            ->required(),
                        Select::make('phone')
                            ->label('电话保留')
                            ->options(['target' => '保留目标客户', 'source' => '保留当前客户'])
                            ->default('target'),
                        Select::make('email')
                            ->label('邮箱保留')
                            ->options(['target' => '保留目标客户', 'source' => '保留当前客户'])
                            ->default('target'),
                        Select::make('website')
                            ->label('网站保留')
                            ->options(['target' => '保留目标客户', 'source' => '保留当前客户'])
                            ->default('target'),
                        Select::make('industry')
                            ->label('行业保留')
                            ->options(['target' => '保留目标客户', 'source' => '保留当前客户'])
                            ->default('target'),
                        Select::make('company_size')
                            ->label('规模保留')
                            ->options(['target' => '保留目标客户', 'source' => '保留当前客户'])
                            ->default('target'),
                        Select::make('registered_address')
                            ->label('地址保留')
                            ->options(['target' => '保留目标客户', 'source' => '保留当前客户'])
                            ->default('target'),
                    ])
                    ->requiresConfirmation()
                    ->action(function (Customer $record, array $data): void {
                        $target = Customer::findOrFail($data['target_customer_id']);
                        app(CustomerMergeService::class)->mergeWithFields($record, $target, [
                            'phone' => $data['phone'] ?? 'target',
                            'email' => $data['email'] ?? 'target',
                            'website' => $data['website'] ?? 'target',
                            'industry' => $data['industry'] ?? 'target',
                            'company_size' => $data['company_size'] ?? 'target',
                            'registered_address' => $data['registered_address'] ?? 'target',
                        ]);

                        Notification::make()->success()->title('客户已合并')->send();
                    }),
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
                RestoreAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageCustomers::route('/'),
            'profile' => CustomerProfile::route('/{record}/profile'),
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

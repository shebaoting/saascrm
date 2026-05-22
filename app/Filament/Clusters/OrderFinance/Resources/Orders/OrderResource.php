<?php

namespace App\Filament\Clusters\OrderFinance\Resources\Orders;

use App\Filament\Clusters\OrderFinance\OrderFinanceCluster;
use App\Filament\Clusters\OrderFinance\Resources\Orders\Pages\ManageOrders;
use App\Filament\Concerns\UsesCrmAccess;
use App\Models\Contact;
use App\Models\Order;
use App\Services\Crm\OrderFinanceService;
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
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class OrderResource extends Resource
{
    use UsesCrmAccess;

    protected static ?string $model = Order::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $navigationLabel = '订单';

    protected static ?string $modelLabel = '订单';

    protected static ?string $pluralModelLabel = '订单';

    protected static ?string $title = '订单';

    protected static bool $hasTitleCaseModelLabel = false;

    protected static ?string $cluster = OrderFinanceCluster::class;

    protected static ?string $recordTitleAttribute = 'order_number';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('order_number')
                    ->default(fn (): string => 'SO'.now()->format('YmdHis'))
                    ->required(),
                Select::make('customer_id')
                    ->relationship('customer', 'name')
                    ->live()
                    ->required(),
                Select::make('contact_id')
                    ->options(fn (Get $get): array => Contact::query()
                        ->where('tenant_id', CrmAccess::tenantId())
                        ->when($get('customer_id'), fn (Builder $query, int|string $customerId): Builder => $query->where('customer_id', $customerId))
                        ->orderBy('name')
                        ->pluck('name', 'id')
                        ->all()),
                Select::make('opportunity_id')
                    ->relationship('opportunity', 'name'),
                Select::make('quote_id')
                    ->relationship('quote', 'title'),
                Select::make('employee_id')
                    ->relationship('employee', 'name')
                    ->default(fn (): ?int => auth()->id()),
                Repeater::make('items')
                    ->label('订单明细')
                    ->relationship('items')
                    ->schema([
                        Select::make('product_id')
                            ->relationship('product', 'name')
                            ->required(),
                        Select::make('product_sku_id')
                            ->relationship('sku', 'sku_code'),
                        TextInput::make('quantity')
                            ->required()
                            ->numeric()
                            ->default(1),
                        TextInput::make('unit_price')
                            ->required()
                            ->numeric()
                            ->default(0)
                            ->prefix('¥'),
                        TextInput::make('cost_price')
                            ->numeric()
                            ->default(0)
                            ->prefix('¥'),
                        TextInput::make('tax_rate')
                            ->numeric()
                            ->default(0)
                            ->suffix('%'),
                    ])
                    ->columns(3)
                    ->columnSpanFull()
                    ->addActionLabel('添加订单明细'),
                TextInput::make('subtotal_amount')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('discount_amount')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('total_amount')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('total_cost')
                    ->required()
                    ->numeric()
                    ->default(0)
                    ->prefix('¥'),
                TextInput::make('gross_profit')
                    ->required()
                    ->numeric()
                    ->default(0),
                Select::make('order_source')
                    ->options(CrmUi::options('order.order_source'))
                    ->required()
                    ->default('sales_entry'),
                Select::make('order_status')
                    ->options(CrmUi::options('order.order_status'))
                    ->required()
                    ->default('draft'),
                Select::make('payment_status')
                    ->options(CrmUi::options('payment_status'))
                    ->required()
                    ->default('unpaid'),
                Repeater::make('paymentPlans')
                    ->label('收款计划')
                    ->relationship('paymentPlans')
                    ->schema([
                        DatePicker::make('plan_date')
                            ->required(),
                        TextInput::make('plan_amount')
                            ->required()
                            ->numeric()
                            ->default(0)
                            ->prefix('¥'),
                        Select::make('status')
                            ->options(CrmUi::options('payment_plan.status'))
                            ->default('pending')
                            ->required(),
                        TextInput::make('notes'),
                    ])
                    ->columns(4)
                    ->columnSpanFull()
                    ->addActionLabel('添加收款计划'),
                Repeater::make('attachments')
                    ->label('合同和凭证')
                    ->relationship('attachments')
                    ->schema([
                        Select::make('category')
                            ->options(CrmUi::options('attachment.category'))
                            ->default('contract')
                            ->required(),
                        FileUpload::make('path')
                            ->disk('local')
                            ->directory(fn (): string => 'tenants/'.CrmAccess::tenantId().'/orders/attachments')
                            ->downloadable()
                            ->openable()
                            ->required(),
                        TextInput::make('name'),
                        Hidden::make('disk')
                            ->default('local'),
                        Hidden::make('user_id')
                            ->default(fn (): ?int => auth()->id()),
                    ])
                    ->columns(3)
                    ->columnSpanFull()
                    ->addActionLabel('添加合同/凭证'),
                DateTimePicker::make('ordered_at')
                    ->required(),
                DateTimePicker::make('completed_at'),
                Textarea::make('notes')
                    ->columnSpanFull(),
                ...CustomFieldUi::formSections('order'),
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
                    ->visible(fn (Order $record): bool => $record->trashed()),
                TextEntry::make('order_number'),
                TextEntry::make('customer.name')
                    ->label('客户'),
                TextEntry::make('contact.name')
                    ->label('联系人')
                    ->placeholder('-'),
                TextEntry::make('opportunity.name')
                    ->label('商机')
                    ->placeholder('-'),
                TextEntry::make('quote.title')
                    ->label('报价单')
                    ->placeholder('-'),
                TextEntry::make('employee.name')
                    ->placeholder('-'),
                TextEntry::make('subtotal_amount')
                    ->numeric(),
                TextEntry::make('discount_amount')
                    ->numeric(),
                TextEntry::make('total_amount')
                    ->numeric(),
                TextEntry::make('total_cost')
                    ->money(),
                TextEntry::make('gross_profit')
                    ->numeric(),
                TextEntry::make('order_source'),
                TextEntry::make('order_status'),
                TextEntry::make('payment_status'),
                RepeatableEntry::make('paymentPlans')
                    ->label('收款计划')
                    ->schema([
                        TextEntry::make('plan_date')
                            ->date(),
                        TextEntry::make('plan_amount')
                            ->money(),
                        TextEntry::make('received_amount')
                            ->money(),
                        TextEntry::make('status'),
                        TextEntry::make('notes')
                            ->placeholder('-'),
                    ])
                    ->columns(5)
                    ->columnSpanFull(),
                RepeatableEntry::make('attachments')
                    ->label('合同和凭证')
                    ->schema([
                        TextEntry::make('category')
                            ->formatStateUsing(fn (mixed $state): ?string => CrmUi::options('attachment.category')[$state] ?? $state),
                        TextEntry::make('name')
                            ->placeholder('-'),
                        TextEntry::make('path')
                            ->placeholder('-'),
                    ])
                    ->columns(3)
                    ->columnSpanFull(),
                TextEntry::make('ordered_at')
                    ->dateTime(),
                TextEntry::make('completed_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('notes')
                    ->placeholder('-')
                    ->columnSpanFull(),
                ...CustomFieldUi::infolistSections('order'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('order_number')
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
                TextColumn::make('order_number')
                    ->searchable(),
                TextColumn::make('customer.name')
                    ->searchable(),
                TextColumn::make('contact.name')
                    ->searchable(),
                TextColumn::make('opportunity.name')
                    ->searchable(),
                TextColumn::make('quote.title')
                    ->searchable(),
                TextColumn::make('employee.name')
                    ->searchable(),
                TextColumn::make('subtotal_amount')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('discount_amount')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('total_amount')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('total_cost')
                    ->money()
                    ->sortable(),
                TextColumn::make('gross_profit')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('order_source')
                    ->searchable(),
                TextColumn::make('order_status')
                    ->searchable(),
                TextColumn::make('payment_status')
                    ->searchable(),
                TextColumn::make('ordered_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('completed_at')
                    ->dateTime()
                    ->sortable(),
                ...CustomFieldUi::tableColumns('order'),
            ])
            ->filters([
                SelectFilter::make('order_status')
                    ->options(CrmUi::options('order.order_status')),
                SelectFilter::make('payment_status')
                    ->options(CrmUi::options('payment_status')),
                SelectFilter::make('customer_id')
                    ->relationship('customer', 'name'),
                TrashedFilter::make(),
                ...CustomFieldUi::tableFilters('order'),
            ])
            ->recordActions([
                Action::make('refresh_finance')
                    ->label('刷新财务')
                    ->icon('heroicon-o-arrow-path')
                    ->action(function (Order $record): void {
                        app(OrderFinanceService::class)->refresh($record);

                        Notification::make()->success()->title('订单财务已刷新')->send();
                    }),
                Action::make('complete')
                    ->label('完成订单')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (Order $record): bool => $record->order_status !== 'completed')
                    ->requiresConfirmation()
                    ->action(function (Order $record): void {
                        $record->forceFill([
                            'order_status' => 'completed',
                            'completed_at' => now(),
                        ])->save();

                        Notification::make()->success()->title('订单已完成')->send();
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
            'index' => ManageOrders::route('/'),
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

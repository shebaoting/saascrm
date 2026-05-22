<?php

namespace App\Filament\Clusters\OrderFinance\Resources\Orders;

use App\Filament\Clusters\OrderFinance\OrderFinanceCluster;
use App\Filament\Clusters\OrderFinance\Resources\Orders\Pages\ManageOrders;
use App\Models\Order;
use App\Services\Crm\OrderFinanceService;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class OrderResource extends Resource
{
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
                    ->required(),
                Select::make('contact_id')
                    ->relationship('contact', 'name'),
                Select::make('opportunity_id')
                    ->relationship('opportunity', 'name'),
                Select::make('quote_id')
                    ->relationship('quote', 'title'),
                Select::make('employee_id')
                    ->relationship('employee', 'name')
                    ->default(fn (): ?int => auth()->id()),
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
                    ->prefix('$'),
                TextInput::make('gross_profit')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('order_source')
                    ->required()
                    ->default('sales_entry'),
                Select::make('order_status')
                    ->options([
                        'draft' => '草稿',
                        'confirmed' => '已确认',
                        'completed' => '已完成',
                        'cancelled' => '已取消',
                    ])
                    ->required()
                    ->default('draft'),
                Select::make('payment_status')
                    ->options([
                        'unpaid' => '未收款',
                        'partial_paid' => '部分收款',
                        'paid' => '已收款',
                        'refunded' => '已退款',
                    ])
                    ->required()
                    ->default('unpaid'),
                DateTimePicker::make('ordered_at')
                    ->required(),
                DateTimePicker::make('completed_at'),
                Textarea::make('notes')
                    ->columnSpanFull(),
                TextInput::make('custom_fields'),
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
                    ->label('Customer'),
                TextEntry::make('contact.name')
                    ->label('Contact')
                    ->placeholder('-'),
                TextEntry::make('opportunity.name')
                    ->label('Opportunity')
                    ->placeholder('-'),
                TextEntry::make('quote.title')
                    ->label('Quote')
                    ->placeholder('-'),
                TextEntry::make('employee_id')
                    ->numeric()
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
                TextEntry::make('ordered_at')
                    ->dateTime(),
                TextEntry::make('completed_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('notes')
                    ->placeholder('-')
                    ->columnSpanFull(),
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
                TextColumn::make('employee_id')
                    ->numeric()
                    ->sortable(),
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
            ])
            ->filters([
                TrashedFilter::make(),
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
                ForceDeleteAction::make(),
                RestoreAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
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

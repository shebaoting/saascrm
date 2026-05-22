<?php

namespace App\Filament\Clusters\OrderFinance\Resources\Payments;

use App\Filament\Clusters\OrderFinance\OrderFinanceCluster;
use App\Filament\Clusters\OrderFinance\Resources\Payments\Pages\ManagePayments;
use App\Filament\Concerns\UsesCrmAccess;
use App\Models\OrderPaymentPlan;
use App\Models\Payment;
use App\Support\CrmAccess;
use App\Support\Filament\CrmUi;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
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

class PaymentResource extends Resource
{
    use UsesCrmAccess;

    protected static ?string $model = Payment::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $navigationLabel = '收款';

    protected static ?string $modelLabel = '收款';

    protected static ?string $pluralModelLabel = '收款';

    protected static ?string $title = '收款';

    protected static bool $hasTitleCaseModelLabel = false;

    protected static ?string $cluster = OrderFinanceCluster::class;

    protected static ?string $recordTitleAttribute = 'status';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('order_id')
                    ->relationship('order', 'order_number')
                    ->live()
                    ->required(),
                Select::make('payment_plan_id')
                    ->options(fn (Get $get): array => OrderPaymentPlan::query()
                        ->where('tenant_id', CrmAccess::tenantId())
                        ->when($get('order_id'), fn (Builder $query, int|string $orderId): Builder => $query->where('order_id', $orderId))
                        ->orderBy('plan_date')
                        ->get()
                        ->mapWithKeys(fn (OrderPaymentPlan $plan): array => [
                            $plan->id => $plan->plan_date?->format('Y-m-d').' / ¥'.number_format((float) $plan->plan_amount, 2),
                        ])
                        ->all()),
                DateTimePicker::make('received_at'),
                TextInput::make('amount')
                    ->required()
                    ->numeric()
                    ->default(0),
                Select::make('status')
                    ->options(CrmUi::options('payment.status'))
                    ->required()
                    ->default('pending'),
                Select::make('payment_method')
                    ->options(CrmUi::options('payment.method')),
                TextInput::make('transaction_no'),
                TextInput::make('notes'),
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
                    ->visible(fn (Payment $record): bool => $record->trashed()),
                TextEntry::make('order.order_number')
                    ->label('订单'),
                TextEntry::make('paymentPlan.plan_date')
                    ->label('收款计划')
                    ->date()
                    ->placeholder('-'),
                TextEntry::make('plan_date')
                    ->date()
                    ->placeholder('-'),
                TextEntry::make('received_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('amount')
                    ->numeric(),
                TextEntry::make('status'),
                TextEntry::make('payment_method')
                    ->placeholder('-'),
                TextEntry::make('transaction_no')
                    ->placeholder('-'),
                TextEntry::make('notes')
                    ->placeholder('-'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('status')
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
                TextColumn::make('order.order_number')
                    ->searchable(),
                TextColumn::make('paymentPlan.plan_date')
                    ->date()
                    ->sortable(),
                TextColumn::make('plan_date')
                    ->date()
                    ->sortable(),
                TextColumn::make('received_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('amount')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('status')
                    ->searchable(),
                TextColumn::make('payment_method')
                    ->searchable(),
                TextColumn::make('transaction_no')
                    ->searchable(),
                TextColumn::make('notes')
                    ->searchable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options(CrmUi::options('payment.status')),
                SelectFilter::make('order_id')
                    ->relationship('order', 'order_number'),
                TrashedFilter::make(),
            ])
            ->recordActions([
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
            'index' => ManagePayments::route('/'),
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

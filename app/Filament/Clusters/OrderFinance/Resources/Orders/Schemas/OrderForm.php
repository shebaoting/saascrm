<?php

namespace App\Filament\Clusters\OrderFinance\Resources\Orders\Schemas;

use App\Models\Contact;
use App\Support\CrmAccess;
use App\Support\Filament\CrmUi;
use App\Support\Filament\CustomFieldUi;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

class OrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
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
                TextInput::make('discount_amount')
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
                        TextInput::make('notes'),
                    ])
                    ->columns(3)
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
                Textarea::make('notes')
                    ->columnSpanFull(),
                ...CustomFieldUi::formSections('order'),
            ]);
    }
}

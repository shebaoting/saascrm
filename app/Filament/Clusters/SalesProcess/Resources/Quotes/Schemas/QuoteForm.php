<?php

namespace App\Filament\Clusters\SalesProcess\Resources\Quotes\Schemas;

use App\Models\Contact;
use App\Support\CrmAccess;
use App\Support\Filament\CustomFieldUi;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

class QuoteForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
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
                Select::make('user_id')
                    ->relationship('creator', 'name')
                    ->default(fn (): ?int => auth()->id())
                    ->required(),
                Select::make('price_book_id')
                    ->relationship('priceBook', 'name'),
                Repeater::make('items')
                    ->label('报价明细')
                    ->relationship('items')
                    ->schema([
                        Select::make('product_id')
                            ->relationship('product', 'name')
                            ->required(),
                        Select::make('product_sku_id')
                            ->relationship('sku', 'sku_code')
                            ->required(),
                        TextInput::make('quantity')
                            ->required()
                            ->numeric()
                            ->default(1),
                        TextInput::make('unit_price')
                            ->numeric()
                            ->default(0)
                            ->prefix('¥'),
                        TextInput::make('discount_amount')
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
                    ->columns(4)
                    ->columnSpanFull()
                    ->addActionLabel('添加报价明细'),
                TextInput::make('discount_amount')
                    ->required()
                    ->numeric()
                    ->default(0),
                DatePicker::make('valid_until'),
                Textarea::make('notes')
                    ->columnSpanFull(),
                ...CustomFieldUi::formSections('quote'),
            ]);
    }
}

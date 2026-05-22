<?php

namespace App\Filament\Clusters\ProductPricing\Resources\PriceBookItems;

use App\Filament\Clusters\ProductPricing\ProductPricingCluster;
use App\Filament\Clusters\ProductPricing\Resources\PriceBookItems\Pages\ManagePriceBookItems;
use App\Models\PriceBookItem;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PriceBookItemResource extends Resource
{
    protected static ?string $model = PriceBookItem::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $navigationLabel = '价格明细';

    protected static ?string $modelLabel = '价格明细';

    protected static ?string $pluralModelLabel = '价格明细';

    protected static ?string $title = '价格明细';

    protected static bool $hasTitleCaseModelLabel = false;

    protected static ?string $cluster = ProductPricingCluster::class;

    protected static ?string $recordTitleAttribute = 'price';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('price_book_id')
                    ->relationship('priceBook', 'name')
                    ->required(),
                TextInput::make('product_sku_id')
                    ->required()
                    ->numeric(),
                TextInput::make('price')
                    ->required()
                    ->numeric()
                    ->default(0)
                    ->prefix('$'),
                TextInput::make('min_price')
                    ->numeric()
                    ->prefix('$'),
                DateTimePicker::make('starts_at'),
                DateTimePicker::make('ends_at'),
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
                TextEntry::make('priceBook.name')
                    ->label('Price book'),
                TextEntry::make('product_sku_id')
                    ->numeric(),
                TextEntry::make('price')
                    ->money(),
                TextEntry::make('min_price')
                    ->money()
                    ->placeholder('-'),
                TextEntry::make('starts_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('ends_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('price')
            ->columns([
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('priceBook.name')
                    ->searchable(),
                TextColumn::make('product_sku_id')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('price')
                    ->money()
                    ->sortable(),
                TextColumn::make('min_price')
                    ->money()
                    ->sortable(),
                TextColumn::make('starts_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('ends_at')
                    ->dateTime()
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
            'index' => ManagePriceBookItems::route('/'),
        ];
    }
}

<?php

namespace App\Filament\Clusters\ProductPricing\Resources\Products;

use App\Filament\Clusters\ProductPricing\ProductPricingCluster;
use App\Filament\Clusters\ProductPricing\Resources\Products\Pages\CreateProduct;
use App\Filament\Clusters\ProductPricing\Resources\Products\Pages\EditProduct;
use App\Filament\Clusters\ProductPricing\Resources\Products\Pages\ListProducts;
use App\Filament\Clusters\ProductPricing\Resources\Products\Pages\ViewProduct;
use App\Filament\Clusters\ProductPricing\Resources\Products\Schemas\ProductForm;
use App\Filament\Clusters\ProductPricing\Resources\Products\Schemas\ProductInfolist;
use App\Filament\Clusters\ProductPricing\Resources\Products\Tables\ProductTable;
use App\Filament\Concerns\UsesCrmAccess;
use App\Models\Product;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ProductResource extends Resource
{
    use UsesCrmAccess;

    protected static ?string $model = Product::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $navigationLabel = '商品';

    protected static ?string $modelLabel = '商品';

    protected static ?string $pluralModelLabel = '商品';

    protected static ?string $title = '商品';

    protected static bool $hasTitleCaseModelLabel = false;

    protected static ?string $cluster = ProductPricingCluster::class;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return ProductForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ProductInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ProductTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListProducts::route('/'),
            'create' => CreateProduct::route('/create'),
            'view' => ViewProduct::route('/{record}'),
            'edit' => EditProduct::route('/{record}/edit'),
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

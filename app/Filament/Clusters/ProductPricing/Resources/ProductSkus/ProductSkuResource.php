<?php

namespace App\Filament\Clusters\ProductPricing\Resources\ProductSkus;

use App\Filament\Clusters\ProductPricing\ProductPricingCluster;
use App\Filament\Clusters\ProductPricing\Resources\ProductSkus\Pages\CreateProductSku;
use App\Filament\Clusters\ProductPricing\Resources\ProductSkus\Pages\EditProductSku;
use App\Filament\Clusters\ProductPricing\Resources\ProductSkus\Pages\ListProductSkus;
use App\Filament\Clusters\ProductPricing\Resources\ProductSkus\Pages\ViewProductSku;
use App\Filament\Clusters\ProductPricing\Resources\ProductSkus\Schemas\ProductSkuForm;
use App\Filament\Clusters\ProductPricing\Resources\ProductSkus\Schemas\ProductSkuInfolist;
use App\Filament\Clusters\ProductPricing\Resources\ProductSkus\Tables\ProductSkuTable;
use App\Filament\Concerns\UsesCrmAccess;
use App\Models\ProductSku;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ProductSkuResource extends Resource
{
    use UsesCrmAccess;

    protected static ?string $model = ProductSku::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $navigationLabel = 'SKU';

    protected static ?string $modelLabel = 'SKU';

    protected static ?string $pluralModelLabel = 'SKU';

    protected static ?string $title = 'SKU';

    protected static bool $hasTitleCaseModelLabel = false;

    protected static ?string $cluster = ProductPricingCluster::class;

    protected static ?string $recordTitleAttribute = 'sku_code';

    public static function form(Schema $schema): Schema
    {
        return ProductSkuForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ProductSkuInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ProductSkuTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListProductSkus::route('/'),
            'create' => CreateProductSku::route('/create'),
            'view' => ViewProductSku::route('/{record}'),
            'edit' => EditProductSku::route('/{record}/edit'),
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

<?php

namespace App\Filament\Clusters\ProductPricing\Resources\ProductGroups;

use App\Filament\Clusters\ProductPricing\ProductPricingCluster;
use App\Filament\Clusters\ProductPricing\Resources\ProductGroups\Pages\CreateProductGroup;
use App\Filament\Clusters\ProductPricing\Resources\ProductGroups\Pages\EditProductGroup;
use App\Filament\Clusters\ProductPricing\Resources\ProductGroups\Pages\ListProductGroups;
use App\Filament\Clusters\ProductPricing\Resources\ProductGroups\Pages\ViewProductGroup;
use App\Filament\Clusters\ProductPricing\Resources\ProductGroups\Schemas\ProductGroupForm;
use App\Filament\Clusters\ProductPricing\Resources\ProductGroups\Schemas\ProductGroupInfolist;
use App\Filament\Clusters\ProductPricing\Resources\ProductGroups\Tables\ProductGroupTable;
use App\Filament\Concerns\UsesCrmAccess;
use App\Models\ProductGroup;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ProductGroupResource extends Resource
{
    use UsesCrmAccess;

    protected static ?string $model = ProductGroup::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $navigationLabel = '商品分组';

    protected static ?string $modelLabel = '商品分组';

    protected static ?string $pluralModelLabel = '商品分组';

    protected static ?string $title = '商品分组';

    protected static bool $hasTitleCaseModelLabel = false;

    protected static ?string $cluster = ProductPricingCluster::class;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return ProductGroupForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ProductGroupInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ProductGroupTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListProductGroups::route('/'),
            'create' => CreateProductGroup::route('/create'),
            'view' => ViewProductGroup::route('/{record}'),
            'edit' => EditProductGroup::route('/{record}/edit'),
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

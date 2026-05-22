<?php

namespace App\Filament\Clusters\ProductPricing\Resources\PriceBookItems;

use App\Filament\Clusters\ProductPricing\ProductPricingCluster;
use App\Filament\Clusters\ProductPricing\Resources\PriceBookItems\Pages\CreatePriceBookItem;
use App\Filament\Clusters\ProductPricing\Resources\PriceBookItems\Pages\EditPriceBookItem;
use App\Filament\Clusters\ProductPricing\Resources\PriceBookItems\Pages\ListPriceBookItems;
use App\Filament\Clusters\ProductPricing\Resources\PriceBookItems\Pages\ViewPriceBookItem;
use App\Filament\Clusters\ProductPricing\Resources\PriceBookItems\Schemas\PriceBookItemForm;
use App\Filament\Clusters\ProductPricing\Resources\PriceBookItems\Schemas\PriceBookItemInfolist;
use App\Filament\Clusters\ProductPricing\Resources\PriceBookItems\Tables\PriceBookItemTable;
use App\Filament\Concerns\UsesCrmAccess;
use App\Models\PriceBookItem;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class PriceBookItemResource extends Resource
{
    use UsesCrmAccess;

    protected static ?string $model = PriceBookItem::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $navigationLabel = '价格明细';

    protected static ?string $modelLabel = '价格明细';

    protected static ?string $pluralModelLabel = '价格明细';

    protected static ?string $title = '价格明细';

    protected static bool $hasTitleCaseModelLabel = false;

    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $cluster = ProductPricingCluster::class;

    protected static ?string $recordTitleAttribute = 'price';

    public static function form(Schema $schema): Schema
    {
        return PriceBookItemForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return PriceBookItemInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PriceBookItemTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPriceBookItems::route('/'),
            'create' => CreatePriceBookItem::route('/create'),
            'view' => ViewPriceBookItem::route('/{record}'),
            'edit' => EditPriceBookItem::route('/{record}/edit'),
        ];
    }
}

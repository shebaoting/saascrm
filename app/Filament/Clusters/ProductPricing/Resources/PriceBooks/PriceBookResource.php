<?php

namespace App\Filament\Clusters\ProductPricing\Resources\PriceBooks;

use App\Filament\Clusters\ProductPricing\ProductPricingCluster;
use App\Filament\Clusters\ProductPricing\Resources\PriceBooks\Pages\CreatePriceBook;
use App\Filament\Clusters\ProductPricing\Resources\PriceBooks\Pages\EditPriceBook;
use App\Filament\Clusters\ProductPricing\Resources\PriceBooks\Pages\ListPriceBooks;
use App\Filament\Clusters\ProductPricing\Resources\PriceBooks\Pages\ViewPriceBook;
use App\Filament\Clusters\ProductPricing\Resources\PriceBooks\Schemas\PriceBookForm;
use App\Filament\Clusters\ProductPricing\Resources\PriceBooks\Schemas\PriceBookInfolist;
use App\Filament\Clusters\ProductPricing\Resources\PriceBooks\Tables\PriceBookTable;
use App\Filament\Concerns\UsesCrmAccess;
use App\Models\PriceBook;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class PriceBookResource extends Resource
{
    use UsesCrmAccess;

    protected static ?string $model = PriceBook::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $navigationLabel = '价格表';

    protected static ?string $modelLabel = '价格表';

    protected static ?string $pluralModelLabel = '价格表';

    protected static ?string $title = '价格表';

    protected static bool $hasTitleCaseModelLabel = false;

    protected static ?string $cluster = ProductPricingCluster::class;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return PriceBookForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return PriceBookInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PriceBookTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPriceBooks::route('/'),
            'create' => CreatePriceBook::route('/create'),
            'view' => ViewPriceBook::route('/{record}'),
            'edit' => EditPriceBook::route('/{record}/edit'),
        ];
    }
}

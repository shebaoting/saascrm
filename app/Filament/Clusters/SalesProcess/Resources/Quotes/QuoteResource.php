<?php

namespace App\Filament\Clusters\SalesProcess\Resources\Quotes;

use App\Filament\Clusters\SalesProcess\Resources\Quotes\Pages\CreateQuote;
use App\Filament\Clusters\SalesProcess\Resources\Quotes\Pages\EditQuote;
use App\Filament\Clusters\SalesProcess\Resources\Quotes\Pages\ListQuotes;
use App\Filament\Clusters\SalesProcess\Resources\Quotes\Pages\ViewQuote;
use App\Filament\Clusters\SalesProcess\Resources\Quotes\Schemas\QuoteForm;
use App\Filament\Clusters\SalesProcess\Resources\Quotes\Schemas\QuoteInfolist;
use App\Filament\Clusters\SalesProcess\Resources\Quotes\Tables\QuoteTable;
use App\Filament\Clusters\SalesProcess\SalesProcessCluster;
use App\Filament\Concerns\UsesCrmAccess;
use App\Models\Quote;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class QuoteResource extends Resource
{
    use UsesCrmAccess;

    protected static ?string $model = Quote::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $navigationLabel = '报价单';

    protected static ?string $modelLabel = '报价单';

    protected static ?string $pluralModelLabel = '报价单';

    protected static ?string $title = '报价单';

    protected static bool $hasTitleCaseModelLabel = false;

    protected static ?string $cluster = SalesProcessCluster::class;

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Schema $schema): Schema
    {
        return QuoteForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return QuoteInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return QuoteTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListQuotes::route('/'),
            'create' => CreateQuote::route('/create'),
            'view' => ViewQuote::route('/{record}'),
            'edit' => EditQuote::route('/{record}/edit'),
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

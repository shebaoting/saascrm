<?php

namespace App\Filament\Clusters\SalesProcess\Resources\Opportunities;

use App\Filament\Clusters\SalesProcess\Resources\Opportunities\Pages\CreateOpportunity;
use App\Filament\Clusters\SalesProcess\Resources\Opportunities\Pages\EditOpportunity;
use App\Filament\Clusters\SalesProcess\Resources\Opportunities\Pages\ListOpportunities;
use App\Filament\Clusters\SalesProcess\Resources\Opportunities\Pages\ViewOpportunity;
use App\Filament\Clusters\SalesProcess\Resources\Opportunities\Schemas\OpportunityForm;
use App\Filament\Clusters\SalesProcess\Resources\Opportunities\Schemas\OpportunityInfolist;
use App\Filament\Clusters\SalesProcess\Resources\Opportunities\Tables\OpportunityTable;
use App\Filament\Clusters\SalesProcess\SalesProcessCluster;
use App\Filament\Concerns\UsesCrmAccess;
use App\Models\Opportunity;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class OpportunityResource extends Resource
{
    use UsesCrmAccess;

    protected static ?string $model = Opportunity::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $navigationLabel = '商机';

    protected static ?string $modelLabel = '商机';

    protected static ?string $pluralModelLabel = '商机';

    protected static ?string $title = '商机';

    protected static bool $hasTitleCaseModelLabel = false;

    protected static ?string $cluster = SalesProcessCluster::class;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return OpportunityForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return OpportunityInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return OpportunityTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListOpportunities::route('/'),
            'create' => CreateOpportunity::route('/create'),
            'view' => ViewOpportunity::route('/{record}'),
            'edit' => EditOpportunity::route('/{record}/edit'),
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

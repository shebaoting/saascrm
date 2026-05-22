<?php

namespace App\Filament\Clusters\LeadCenter\Resources\Leads;

use App\Filament\Clusters\LeadCenter\LeadCenterCluster;
use App\Filament\Clusters\LeadCenter\Resources\Leads\Pages\CreateLead;
use App\Filament\Clusters\LeadCenter\Resources\Leads\Pages\EditLead;
use App\Filament\Clusters\LeadCenter\Resources\Leads\Pages\ListLeads;
use App\Filament\Clusters\LeadCenter\Resources\Leads\Pages\ViewLead;
use App\Filament\Clusters\LeadCenter\Resources\Leads\Schemas\LeadForm;
use App\Filament\Clusters\LeadCenter\Resources\Leads\Schemas\LeadInfolist;
use App\Filament\Clusters\LeadCenter\Resources\Leads\Tables\LeadTable;
use App\Filament\Concerns\UsesCrmAccess;
use App\Models\Lead;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class LeadResource extends Resource
{
    use UsesCrmAccess;

    protected static ?string $model = Lead::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $navigationLabel = '全部线索';

    protected static ?string $modelLabel = '全部线索';

    protected static ?string $pluralModelLabel = '全部线索';

    protected static ?string $title = '全部线索';

    protected static bool $hasTitleCaseModelLabel = false;

    protected static ?string $cluster = LeadCenterCluster::class;

    protected static ?string $recordTitleAttribute = 'company_name';

    public static function form(Schema $schema): Schema
    {
        return LeadForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return LeadInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return LeadTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListLeads::route('/'),
            'create' => CreateLead::route('/create'),
            'view' => ViewLead::route('/{record}'),
            'edit' => EditLead::route('/{record}/edit'),
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

<?php

namespace App\Filament\Clusters\LeadCenter\Resources\AssignmentRules;

use App\Filament\Clusters\LeadCenter\LeadCenterCluster;
use App\Filament\Clusters\LeadCenter\Resources\AssignmentRules\Pages\CreateAssignmentRule;
use App\Filament\Clusters\LeadCenter\Resources\AssignmentRules\Pages\EditAssignmentRule;
use App\Filament\Clusters\LeadCenter\Resources\AssignmentRules\Pages\ListAssignmentRules;
use App\Filament\Clusters\LeadCenter\Resources\AssignmentRules\Pages\ViewAssignmentRule;
use App\Filament\Clusters\LeadCenter\Resources\AssignmentRules\Schemas\AssignmentRuleForm;
use App\Filament\Clusters\LeadCenter\Resources\AssignmentRules\Schemas\AssignmentRuleInfolist;
use App\Filament\Clusters\LeadCenter\Resources\AssignmentRules\Tables\AssignmentRuleTable;
use App\Filament\Concerns\UsesCrmAccess;
use App\Models\AssignmentRule;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class AssignmentRuleResource extends Resource
{
    use UsesCrmAccess;

    protected static ?string $model = AssignmentRule::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $navigationLabel = '分配规则';

    protected static ?string $modelLabel = '分配规则';

    protected static ?string $pluralModelLabel = '分配规则';

    protected static ?string $title = '分配规则';

    protected static bool $hasTitleCaseModelLabel = false;

    protected static bool $shouldRegisterNavigation = true;

    protected static ?string $cluster = LeadCenterCluster::class;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return AssignmentRuleForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return AssignmentRuleInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AssignmentRuleTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAssignmentRules::route('/'),
            'create' => CreateAssignmentRule::route('/create'),
            'view' => ViewAssignmentRule::route('/{record}'),
            'edit' => EditAssignmentRule::route('/{record}/edit'),
        ];
    }
}

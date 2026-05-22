<?php

namespace App\Filament\Clusters\SystemSettings\Resources\BusinessNumberRules;

use App\Filament\Clusters\SystemSettings\Resources\BusinessNumberRules\Pages\CreateBusinessNumberRule;
use App\Filament\Clusters\SystemSettings\Resources\BusinessNumberRules\Pages\EditBusinessNumberRule;
use App\Filament\Clusters\SystemSettings\Resources\BusinessNumberRules\Pages\ListBusinessNumberRules;
use App\Filament\Clusters\SystemSettings\Resources\BusinessNumberRules\Pages\ViewBusinessNumberRule;
use App\Filament\Clusters\SystemSettings\Resources\BusinessNumberRules\Schemas\BusinessNumberRuleForm;
use App\Filament\Clusters\SystemSettings\Resources\BusinessNumberRules\Schemas\BusinessNumberRuleInfolist;
use App\Filament\Clusters\SystemSettings\Resources\BusinessNumberRules\Tables\BusinessNumberRuleTable;
use App\Filament\Clusters\SystemSettings\SystemSettingsCluster;
use App\Filament\Concerns\UsesCrmAccess;
use App\Models\BusinessNumberRule;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class BusinessNumberRuleResource extends Resource
{
    use UsesCrmAccess;

    protected static ?string $model = BusinessNumberRule::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedHashtag;

    protected static ?string $navigationLabel = '编号规则';

    protected static ?string $modelLabel = '编号规则';

    protected static ?string $pluralModelLabel = '编号规则';

    protected static ?string $title = '编号规则';

    protected static bool $hasTitleCaseModelLabel = false;

    protected static ?string $cluster = SystemSettingsCluster::class;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return BusinessNumberRuleForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return BusinessNumberRuleInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return BusinessNumberRuleTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListBusinessNumberRules::route('/'),
            'create' => CreateBusinessNumberRule::route('/create'),
            'view' => ViewBusinessNumberRule::route('/{record}'),
            'edit' => EditBusinessNumberRule::route('/{record}/edit'),
        ];
    }
}

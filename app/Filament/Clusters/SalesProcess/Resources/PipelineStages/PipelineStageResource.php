<?php

namespace App\Filament\Clusters\SalesProcess\Resources\PipelineStages;

use App\Filament\Clusters\SalesProcess\Resources\PipelineStages\Pages\CreatePipelineStage;
use App\Filament\Clusters\SalesProcess\Resources\PipelineStages\Pages\EditPipelineStage;
use App\Filament\Clusters\SalesProcess\Resources\PipelineStages\Pages\ListPipelineStages;
use App\Filament\Clusters\SalesProcess\Resources\PipelineStages\Pages\ViewPipelineStage;
use App\Filament\Clusters\SalesProcess\Resources\PipelineStages\Schemas\PipelineStageForm;
use App\Filament\Clusters\SalesProcess\Resources\PipelineStages\Schemas\PipelineStageInfolist;
use App\Filament\Clusters\SalesProcess\Resources\PipelineStages\Tables\PipelineStageTable;
use App\Filament\Clusters\SalesProcess\SalesProcessCluster;
use App\Filament\Concerns\UsesCrmAccess;
use App\Models\PipelineStage;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class PipelineStageResource extends Resource
{
    use UsesCrmAccess;

    protected static ?string $model = PipelineStage::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $navigationLabel = '销售阶段';

    protected static ?string $modelLabel = '销售阶段';

    protected static ?string $pluralModelLabel = '销售阶段';

    protected static ?string $title = '销售阶段';

    protected static bool $hasTitleCaseModelLabel = false;

    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $cluster = SalesProcessCluster::class;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return PipelineStageForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return PipelineStageInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PipelineStageTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPipelineStages::route('/'),
            'create' => CreatePipelineStage::route('/create'),
            'view' => ViewPipelineStage::route('/{record}'),
            'edit' => EditPipelineStage::route('/{record}/edit'),
        ];
    }
}

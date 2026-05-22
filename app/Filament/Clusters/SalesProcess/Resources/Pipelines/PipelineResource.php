<?php

namespace App\Filament\Clusters\SalesProcess\Resources\Pipelines;

use App\Filament\Clusters\SalesProcess\Resources\Pipelines\Pages\CreatePipeline;
use App\Filament\Clusters\SalesProcess\Resources\Pipelines\Pages\EditPipeline;
use App\Filament\Clusters\SalesProcess\Resources\Pipelines\Pages\ListPipelines;
use App\Filament\Clusters\SalesProcess\Resources\Pipelines\Pages\ViewPipeline;
use App\Filament\Clusters\SalesProcess\Resources\Pipelines\Schemas\PipelineForm;
use App\Filament\Clusters\SalesProcess\Resources\Pipelines\Schemas\PipelineInfolist;
use App\Filament\Clusters\SalesProcess\Resources\Pipelines\Tables\PipelineTable;
use App\Filament\Clusters\SalesProcess\SalesProcessCluster;
use App\Filament\Concerns\UsesCrmAccess;
use App\Models\Pipeline;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PipelineResource extends Resource
{
    use UsesCrmAccess;

    protected static ?string $model = Pipeline::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $navigationLabel = '销售管道';

    protected static ?string $modelLabel = '销售管道';

    protected static ?string $pluralModelLabel = '销售管道';

    protected static ?string $title = '销售管道';

    protected static bool $hasTitleCaseModelLabel = false;

    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $cluster = SalesProcessCluster::class;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return PipelineForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return PipelineInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PipelineTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPipelines::route('/'),
            'create' => CreatePipeline::route('/create'),
            'view' => ViewPipeline::route('/{record}'),
            'edit' => EditPipeline::route('/{record}/edit'),
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

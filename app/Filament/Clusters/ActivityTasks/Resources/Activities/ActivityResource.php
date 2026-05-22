<?php

namespace App\Filament\Clusters\ActivityTasks\Resources\Activities;

use App\Filament\Clusters\ActivityTasks\ActivityTasksCluster;
use App\Filament\Clusters\ActivityTasks\Resources\Activities\Pages\CreateActivity;
use App\Filament\Clusters\ActivityTasks\Resources\Activities\Pages\EditActivity;
use App\Filament\Clusters\ActivityTasks\Resources\Activities\Pages\ListActivities;
use App\Filament\Clusters\ActivityTasks\Resources\Activities\Pages\ViewActivity;
use App\Filament\Clusters\ActivityTasks\Resources\Activities\Schemas\ActivityForm;
use App\Filament\Clusters\ActivityTasks\Resources\Activities\Schemas\ActivityInfolist;
use App\Filament\Clusters\ActivityTasks\Resources\Activities\Tables\ActivityTable;
use App\Filament\Concerns\UsesCrmAccess;
use App\Models\Activity;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ActivityResource extends Resource
{
    use UsesCrmAccess;

    protected static ?string $model = Activity::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $navigationLabel = '活动时间线';

    protected static ?string $modelLabel = '活动时间线';

    protected static ?string $pluralModelLabel = '活动时间线';

    protected static ?string $title = '活动时间线';

    protected static bool $hasTitleCaseModelLabel = false;

    protected static ?string $cluster = ActivityTasksCluster::class;

    protected static ?string $recordTitleAttribute = 'subject';

    public static function form(Schema $schema): Schema
    {
        return ActivityForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ActivityInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ActivityTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListActivities::route('/'),
            'create' => CreateActivity::route('/create'),
            'view' => ViewActivity::route('/{record}'),
            'edit' => EditActivity::route('/{record}/edit'),
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

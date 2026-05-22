<?php

namespace App\Filament\Clusters\ActivityTasks\Resources\Tasks;

use App\Filament\Clusters\ActivityTasks\ActivityTasksCluster;
use App\Filament\Clusters\ActivityTasks\Resources\Tasks\Pages\CreateTask;
use App\Filament\Clusters\ActivityTasks\Resources\Tasks\Pages\EditTask;
use App\Filament\Clusters\ActivityTasks\Resources\Tasks\Pages\ListTasks;
use App\Filament\Clusters\ActivityTasks\Resources\Tasks\Pages\ViewTask;
use App\Filament\Clusters\ActivityTasks\Resources\Tasks\Schemas\TaskForm;
use App\Filament\Clusters\ActivityTasks\Resources\Tasks\Schemas\TaskInfolist;
use App\Filament\Clusters\ActivityTasks\Resources\Tasks\Tables\TaskTable;
use App\Filament\Concerns\UsesCrmAccess;
use App\Models\Task;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class TaskResource extends Resource
{
    use UsesCrmAccess;

    protected static ?string $model = Task::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $navigationLabel = '任务';

    protected static ?string $modelLabel = '任务';

    protected static ?string $pluralModelLabel = '任务';

    protected static ?string $title = '任务';

    protected static bool $hasTitleCaseModelLabel = false;

    protected static ?string $cluster = ActivityTasksCluster::class;

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Schema $schema): Schema
    {
        return TaskForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return TaskInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TaskTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTasks::route('/'),
            'create' => CreateTask::route('/create'),
            'view' => ViewTask::route('/{record}'),
            'edit' => EditTask::route('/{record}/edit'),
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

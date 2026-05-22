<?php

namespace App\Filament\Platform\Clusters\Operations\Resources\FailedJobs;

use App\Filament\Platform\Clusters\Operations\OperationsCluster;
use App\Filament\Platform\Clusters\Operations\Resources\FailedJobs\Pages\ManageFailedJobs;
use App\Models\FailedJob;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Artisan;

class FailedJobResource extends Resource
{
    protected static ?string $model = FailedJob::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedExclamationTriangle;

    protected static ?string $navigationLabel = '失败任务';

    protected static ?string $modelLabel = '失败任务';

    protected static ?string $pluralModelLabel = '失败任务';

    protected static ?string $title = '失败任务';

    protected static bool $hasTitleCaseModelLabel = false;

    protected static ?string $cluster = OperationsCluster::class;

    public static function canCreate(): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('uuid')
                    ->label('UUID')
                    ->searchable()
                    ->limit(12),
                TextColumn::make('connection')
                    ->label('连接')
                    ->searchable(),
                TextColumn::make('queue')
                    ->label('队列')
                    ->searchable(),
                TextColumn::make('exception')
                    ->label('错误')
                    ->limit(80)
                    ->wrap(),
                TextColumn::make('failed_at')
                    ->label('失败时间')
                    ->dateTime()
                    ->sortable(),
            ])
            ->recordActions([
                Action::make('retry')
                    ->label('重试')
                    ->icon('heroicon-o-arrow-path')
                    ->requiresConfirmation()
                    ->action(function (FailedJob $record): void {
                        Artisan::call('queue:retry', ['id' => [$record->uuid]]);

                        Notification::make()
                            ->success()
                            ->title('失败任务已重新入队')
                            ->send();
                    }),
                Action::make('forget')
                    ->label('清理')
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(function (FailedJob $record): void {
                        Artisan::call('queue:forget', ['id' => $record->uuid]);

                        Notification::make()
                            ->success()
                            ->title('失败任务已清理')
                            ->send();
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('failed_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageFailedJobs::route('/'),
        ];
    }
}

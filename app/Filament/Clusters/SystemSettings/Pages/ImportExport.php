<?php

namespace App\Filament\Clusters\SystemSettings\Pages;

use App\Filament\Clusters\SystemSettings\SystemSettingsCluster;
use App\Services\Crm\DataPortService;
use App\Support\CrmMetrics;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class ImportExport extends Page
{
    protected string $view = 'filament.pages.crm-metric-page';

    protected static ?string $cluster = SystemSettingsCluster::class;

    protected static ?string $navigationLabel = '导入导出';

    protected static ?string $title = '导入导出';

    protected static ?int $navigationSort = 91;

    protected function getViewData(): array
    {
        return CrmMetrics::importExport();
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('import_csv')
                ->label('导入 CSV')
                ->icon('heroicon-o-arrow-up-tray')
                ->form([
                    Select::make('module')
                        ->label('导入对象')
                        ->options(DataPortService::importModules())
                        ->required(),
                    FileUpload::make('file')
                        ->label('CSV 文件')
                        ->disk('local')
                        ->directory(fn (): string => 'tenants/'.Filament::getTenant()->getKey().'/imports')
                        ->acceptedFileTypes(['text/csv', 'text/plain', 'application/vnd.ms-excel'])
                        ->required(),
                ])
                ->action(function (array $data): void {
                    $tenant = Filament::getTenant();
                    $import = app(DataPortService::class)->import($tenant->getKey(), auth()->user(), $data['module'], $data['file']);

                    Notification::make()
                        ->success()
                        ->title('导入完成')
                        ->body('成功 '.$import->successful_rows.' 行，失败 '.($import->processed_rows - $import->successful_rows).' 行。')
                        ->send();
                }),
            Action::make('export_csv')
                ->label('导出 CSV')
                ->icon('heroicon-o-arrow-down-tray')
                ->form([
                    Select::make('module')
                        ->label('导出对象')
                        ->options(DataPortService::exportModules())
                        ->required(),
                ])
                ->action(function (array $data): void {
                    $tenant = Filament::getTenant();
                    $export = app(DataPortService::class)->export($tenant->getKey(), auth()->user(), $data['module']);

                    Notification::make()
                        ->success()
                        ->title('导出完成')
                        ->body("已生成 {$export->file_name}，共 {$export->total_rows} 行。")
                        ->send();
                }),
        ];
    }
}

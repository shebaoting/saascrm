<?php

namespace App\Filament\Platform\Clusters\Operations\Resources\AuditLogs;

use App\Filament\Platform\Clusters\Operations\OperationsCluster;
use App\Filament\Platform\Clusters\Operations\Resources\AuditLogs\Pages\CreateAuditLog;
use App\Filament\Platform\Clusters\Operations\Resources\AuditLogs\Pages\EditAuditLog;
use App\Filament\Platform\Clusters\Operations\Resources\AuditLogs\Pages\ListAuditLogs;
use App\Filament\Platform\Clusters\Operations\Resources\AuditLogs\Pages\ViewAuditLog;
use App\Filament\Platform\Clusters\Operations\Resources\AuditLogs\Schemas\AuditLogForm;
use App\Filament\Platform\Clusters\Operations\Resources\AuditLogs\Schemas\AuditLogInfolist;
use App\Filament\Platform\Clusters\Operations\Resources\AuditLogs\Tables\AuditLogTable;
use App\Models\AuditLog;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class AuditLogResource extends Resource
{
    protected static ?string $model = AuditLog::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $navigationLabel = '审计日志';

    protected static ?string $modelLabel = '审计日志';

    protected static ?string $pluralModelLabel = '审计日志';

    protected static ?string $title = '审计日志';

    protected static bool $hasTitleCaseModelLabel = false;

    protected static ?string $cluster = OperationsCluster::class;

    protected static ?string $recordTitleAttribute = 'action';

    public static function form(Schema $schema): Schema
    {
        return AuditLogForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return AuditLogInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AuditLogTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAuditLogs::route('/'),
            'create' => CreateAuditLog::route('/create'),
            'view' => ViewAuditLog::route('/{record}'),
            'edit' => EditAuditLog::route('/{record}/edit'),
        ];
    }
}

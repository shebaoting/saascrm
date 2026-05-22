<?php

namespace App\Filament\Clusters\SalesProcess\Resources\QuoteApprovalRequests;

use App\Filament\Clusters\SalesProcess\Resources\QuoteApprovalRequests\Pages\CreateQuoteApprovalRequest;
use App\Filament\Clusters\SalesProcess\Resources\QuoteApprovalRequests\Pages\EditQuoteApprovalRequest;
use App\Filament\Clusters\SalesProcess\Resources\QuoteApprovalRequests\Pages\ListQuoteApprovalRequests;
use App\Filament\Clusters\SalesProcess\Resources\QuoteApprovalRequests\Pages\ViewQuoteApprovalRequest;
use App\Filament\Clusters\SalesProcess\Resources\QuoteApprovalRequests\Schemas\QuoteApprovalRequestForm;
use App\Filament\Clusters\SalesProcess\Resources\QuoteApprovalRequests\Schemas\QuoteApprovalRequestInfolist;
use App\Filament\Clusters\SalesProcess\Resources\QuoteApprovalRequests\Tables\QuoteApprovalRequestTable;
use App\Filament\Clusters\SalesProcess\SalesProcessCluster;
use App\Filament\Concerns\UsesCrmAccess;
use App\Models\QuoteApprovalRequest;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class QuoteApprovalRequestResource extends Resource
{
    use UsesCrmAccess;

    protected static ?string $model = QuoteApprovalRequest::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $navigationLabel = '报价审批';

    protected static ?string $modelLabel = '报价审批';

    protected static ?string $pluralModelLabel = '报价审批';

    protected static ?string $title = '报价审批';

    protected static bool $hasTitleCaseModelLabel = false;

    protected static ?string $cluster = SalesProcessCluster::class;

    protected static ?string $recordTitleAttribute = 'status';

    public static function canCreate(): bool
    {
        return false;
    }

    public static function form(Schema $schema): Schema
    {
        return QuoteApprovalRequestForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return QuoteApprovalRequestInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return QuoteApprovalRequestTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListQuoteApprovalRequests::route('/'),
            'create' => CreateQuoteApprovalRequest::route('/create'),
            'view' => ViewQuoteApprovalRequest::route('/{record}'),
            'edit' => EditQuoteApprovalRequest::route('/{record}/edit'),
        ];
    }
}

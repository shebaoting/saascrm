<?php

namespace App\Filament\Clusters\SalesProcess\Resources\QuoteApprovalRequests;

use App\Filament\Clusters\SalesProcess\Resources\QuoteApprovalRequests\Pages\ManageQuoteApprovalRequests;
use App\Filament\Clusters\SalesProcess\SalesProcessCluster;
use App\Models\QuoteApprovalRequest;
use App\Support\Filament\CrmUi;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class QuoteApprovalRequestResource extends Resource
{
    protected static ?string $model = QuoteApprovalRequest::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $navigationLabel = '报价审批';

    protected static ?string $modelLabel = '报价审批';

    protected static ?string $pluralModelLabel = '报价审批';

    protected static ?string $title = '报价审批';

    protected static bool $hasTitleCaseModelLabel = false;

    protected static ?string $cluster = SalesProcessCluster::class;

    protected static ?string $recordTitleAttribute = 'status';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('quote_id')
                    ->relationship('quote', 'title')
                    ->required(),
                Select::make('requested_by')
                    ->relationship('requester', 'name')
                    ->default(fn (): ?int => auth()->id())
                    ->required(),
                Select::make('approver_id')
                    ->relationship('approver', 'name'),
                Select::make('status')
                    ->options(CrmUi::options('quote_approval.status'))
                    ->required()
                    ->default('pending'),
                TextInput::make('reason'),
                TextInput::make('approval_comment'),
                DateTimePicker::make('requested_at')
                    ->required(),
                DateTimePicker::make('approved_at'),
                DateTimePicker::make('rejected_at'),
            ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('quote.title')
                    ->label('报价单'),
                TextEntry::make('requester.name'),
                TextEntry::make('approver.name')
                    ->label('审批人')
                    ->placeholder('-'),
                TextEntry::make('status'),
                TextEntry::make('reason')
                    ->placeholder('-'),
                TextEntry::make('approval_comment')
                    ->placeholder('-'),
                TextEntry::make('requested_at')
                    ->dateTime(),
                TextEntry::make('approved_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('rejected_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('status')
            ->columns([
                TextColumn::make('quote.title')
                    ->searchable(),
                TextColumn::make('requester.name')
                    ->searchable(),
                TextColumn::make('approver.name')
                    ->searchable(),
                TextColumn::make('status')
                    ->searchable(),
                TextColumn::make('reason')
                    ->searchable(),
                TextColumn::make('approval_comment')
                    ->searchable(),
                TextColumn::make('requested_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('approved_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('rejected_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageQuoteApprovalRequests::route('/'),
        ];
    }
}

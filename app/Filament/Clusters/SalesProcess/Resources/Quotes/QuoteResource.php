<?php

namespace App\Filament\Clusters\SalesProcess\Resources\Quotes;

use App\Filament\Clusters\SalesProcess\Resources\Quotes\Pages\ManageQuotes;
use App\Filament\Clusters\SalesProcess\SalesProcessCluster;
use App\Models\Quote;
use App\Services\Crm\QuoteCalculatorService;
use App\Services\Crm\QuotePdfService;
use App\Services\Crm\QuoteToOrderService;
use App\Support\Filament\CrmUi;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class QuoteResource extends Resource
{
    protected static ?string $model = Quote::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $navigationLabel = '报价单';

    protected static ?string $modelLabel = '报价单';

    protected static ?string $pluralModelLabel = '报价单';

    protected static ?string $title = '报价单';

    protected static bool $hasTitleCaseModelLabel = false;

    protected static ?string $cluster = SalesProcessCluster::class;

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('quote_number')
                    ->default(fn (): string => 'QT'.now()->format('YmdHis'))
                    ->required(),
                TextInput::make('version')
                    ->required()
                    ->numeric()
                    ->default(1),
                TextInput::make('title')
                    ->required(),
                Select::make('customer_id')
                    ->relationship('customer', 'name')
                    ->required(),
                Select::make('contact_id')
                    ->relationship('contact', 'name'),
                Select::make('opportunity_id')
                    ->relationship('opportunity', 'name'),
                Select::make('user_id')
                    ->relationship('creator', 'name')
                    ->default(fn (): ?int => auth()->id())
                    ->required(),
                Select::make('price_book_id')
                    ->relationship('priceBook', 'name'),
                TextInput::make('subtotal_amount')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('discount_amount')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('total_amount')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('total_cost')
                    ->required()
                    ->numeric()
                    ->default(0)
                    ->prefix('$'),
                TextInput::make('total_profit')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('total_tax')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('profit_margin')
                    ->required()
                    ->numeric()
                    ->default(0),
                Select::make('status')
                    ->options(CrmUi::options('quote.status'))
                    ->required()
                    ->default('draft'),
                DatePicker::make('valid_until'),
                Textarea::make('notes')
                    ->columnSpanFull(),
                TextInput::make('custom_fields'),
                TextInput::make('pdf_path'),
                DateTimePicker::make('accepted_at'),
            ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('deleted_at')
                    ->dateTime()
                    ->visible(fn (Quote $record): bool => $record->trashed()),
                TextEntry::make('quote_number'),
                TextEntry::make('version')
                    ->numeric(),
                TextEntry::make('title'),
                TextEntry::make('customer.name')
                    ->label('客户'),
                TextEntry::make('contact.name')
                    ->label('联系人')
                    ->placeholder('-'),
                TextEntry::make('opportunity.name')
                    ->label('商机')
                    ->placeholder('-'),
                TextEntry::make('creator.name'),
                TextEntry::make('priceBook.name')
                    ->placeholder('-'),
                TextEntry::make('subtotal_amount')
                    ->numeric(),
                TextEntry::make('discount_amount')
                    ->numeric(),
                TextEntry::make('total_amount')
                    ->numeric(),
                TextEntry::make('total_cost')
                    ->money(),
                TextEntry::make('total_profit')
                    ->numeric(),
                TextEntry::make('total_tax')
                    ->numeric(),
                TextEntry::make('profit_margin')
                    ->numeric(),
                TextEntry::make('status'),
                TextEntry::make('valid_until')
                    ->date()
                    ->placeholder('-'),
                TextEntry::make('notes')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('pdf_path')
                    ->placeholder('-'),
                TextEntry::make('accepted_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->columns([
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('quote_number')
                    ->searchable(),
                TextColumn::make('version')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('title')
                    ->searchable(),
                TextColumn::make('customer.name')
                    ->searchable(),
                TextColumn::make('contact.name')
                    ->searchable(),
                TextColumn::make('opportunity.name')
                    ->searchable(),
                TextColumn::make('creator.name')
                    ->searchable(),
                TextColumn::make('priceBook.name')
                    ->searchable(),
                TextColumn::make('subtotal_amount')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('discount_amount')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('total_amount')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('total_cost')
                    ->money()
                    ->sortable(),
                TextColumn::make('total_profit')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('total_tax')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('profit_margin')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('status')
                    ->searchable(),
                TextColumn::make('valid_until')
                    ->date()
                    ->sortable(),
                TextColumn::make('pdf_path')
                    ->searchable(),
                TextColumn::make('accepted_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->recordActions([
                Action::make('recalculate')
                    ->label('重算金额')
                    ->icon('heroicon-o-calculator')
                    ->action(function (Quote $record): void {
                        app(QuoteCalculatorService::class)->recalculate($record);

                        Notification::make()->success()->title('报价金额已重算')->send();
                    }),
                Action::make('approve')
                    ->label('审批通过')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (Quote $record): bool => in_array($record->status, ['draft', 'pending_approval'], true))
                    ->requiresConfirmation()
                    ->action(function (Quote $record): void {
                        $record->forceFill(['status' => 'approved'])->save();

                        Notification::make()->success()->title('报价已批准')->send();
                    }),
                Action::make('generate_pdf')
                    ->label('生成 PDF')
                    ->icon('heroicon-o-document-arrow-down')
                    ->action(function (Quote $record): void {
                        app(QuotePdfService::class)->generate($record);

                        Notification::make()->success()->title('报价 PDF 已生成')->send();
                    }),
                Action::make('convert_order')
                    ->label('转订单')
                    ->icon('heroicon-o-document-check')
                    ->color('success')
                    ->visible(fn (Quote $record): bool => $record->status !== 'accepted')
                    ->requiresConfirmation()
                    ->action(function (Quote $record): void {
                        app(QuoteToOrderService::class)->convert($record);

                        Notification::make()->success()->title('订单已生成')->send();
                    }),
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
                ForceDeleteAction::make(),
                RestoreAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageQuotes::route('/'),
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

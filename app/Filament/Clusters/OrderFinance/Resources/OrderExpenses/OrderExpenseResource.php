<?php

namespace App\Filament\Clusters\OrderFinance\Resources\OrderExpenses;

use App\Filament\Clusters\OrderFinance\OrderFinanceCluster;
use App\Filament\Clusters\OrderFinance\Resources\OrderExpenses\Pages\ManageOrderExpenses;
use App\Filament\Concerns\UsesCrmAccess;
use App\Models\OrderExpense;
use App\Support\Filament\CrmUi;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class OrderExpenseResource extends Resource
{
    use UsesCrmAccess;

    protected static ?string $model = OrderExpense::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $navigationLabel = '支出';

    protected static ?string $modelLabel = '支出';

    protected static ?string $pluralModelLabel = '支出';

    protected static ?string $title = '支出';

    protected static bool $hasTitleCaseModelLabel = false;

    protected static ?string $cluster = OrderFinanceCluster::class;

    protected static ?string $recordTitleAttribute = 'reason';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('order_id')
                    ->relationship('order', 'order_number')
                    ->required(),
                DatePicker::make('expense_date')
                    ->required(),
                TextInput::make('amount')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('category'),
                TextInput::make('reason')
                    ->required(),
                Select::make('status')
                    ->options(CrmUi::options('order_expense.status'))
                    ->required()
                    ->default('pending'),
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
                    ->visible(fn (OrderExpense $record): bool => $record->trashed()),
                TextEntry::make('order.order_number')
                    ->label('订单'),
                TextEntry::make('expense_date')
                    ->date(),
                TextEntry::make('amount')
                    ->numeric(),
                TextEntry::make('category')
                    ->placeholder('-'),
                TextEntry::make('reason'),
                TextEntry::make('status'),
                TextEntry::make('approvedBy.name')
                    ->placeholder('-'),
                TextEntry::make('approved_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('reason')
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
                TextColumn::make('order.order_number')
                    ->searchable(),
                TextColumn::make('expense_date')
                    ->date()
                    ->sortable(),
                TextColumn::make('amount')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('category')
                    ->searchable(),
                TextColumn::make('reason')
                    ->searchable(),
                TextColumn::make('status')
                    ->searchable(),
                TextColumn::make('approvedBy.name')
                    ->searchable(),
                TextColumn::make('approved_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
                RestoreAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageOrderExpenses::route('/'),
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

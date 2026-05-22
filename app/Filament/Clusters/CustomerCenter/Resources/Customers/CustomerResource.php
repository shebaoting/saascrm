<?php

namespace App\Filament\Clusters\CustomerCenter\Resources\Customers;

use App\Filament\Clusters\CustomerCenter\CustomerCenterCluster;
use App\Filament\Clusters\CustomerCenter\Resources\Customers\Pages\ManageCustomers;
use App\Models\Customer;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DateTimePicker;
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

class CustomerResource extends Resource
{
    protected static ?string $model = Customer::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $navigationLabel = '全部客户';

    protected static ?string $modelLabel = '全部客户';

    protected static ?string $pluralModelLabel = '全部客户';

    protected static ?string $title = '全部客户';

    protected static bool $hasTitleCaseModelLabel = false;

    protected static ?string $cluster = CustomerCenterCluster::class;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                TextInput::make('short_name'),
                TextInput::make('customer_type')
                    ->required()
                    ->default('company'),
                TextInput::make('lifecycle_stage')
                    ->required()
                    ->default('new'),
                TextInput::make('owner_user_id')
                    ->numeric(),
                TextInput::make('source'),
                TextInput::make('tags'),
                TextInput::make('country_code'),
                TextInput::make('area_id'),
                TextInput::make('email')
                    ->label('Email address')
                    ->email(),
                TextInput::make('phone')
                    ->tel(),
                TextInput::make('registered_address'),
                TextInput::make('website')
                    ->url(),
                TextInput::make('industry'),
                TextInput::make('company_size'),
                TextInput::make('annual_revenue')
                    ->numeric(),
                DateTimePicker::make('pool_entered_at'),
                DateTimePicker::make('last_activity_at'),
                DateTimePicker::make('next_activity_at'),
                DateTimePicker::make('first_order_at'),
                DateTimePicker::make('last_order_at'),
                TextInput::make('custom_fields'),
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
                    ->visible(fn (Customer $record): bool => $record->trashed()),
                TextEntry::make('name'),
                TextEntry::make('short_name')
                    ->placeholder('-'),
                TextEntry::make('customer_type'),
                TextEntry::make('lifecycle_stage'),
                TextEntry::make('owner_user_id')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('source')
                    ->placeholder('-'),
                TextEntry::make('country_code')
                    ->placeholder('-'),
                TextEntry::make('area_id')
                    ->placeholder('-'),
                TextEntry::make('email')
                    ->label('Email address')
                    ->placeholder('-'),
                TextEntry::make('phone')
                    ->placeholder('-'),
                TextEntry::make('registered_address')
                    ->placeholder('-'),
                TextEntry::make('website')
                    ->placeholder('-'),
                TextEntry::make('industry')
                    ->placeholder('-'),
                TextEntry::make('company_size')
                    ->placeholder('-'),
                TextEntry::make('annual_revenue')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('pool_entered_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('last_activity_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('next_activity_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('first_order_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('last_order_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
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
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('short_name')
                    ->searchable(),
                TextColumn::make('customer_type')
                    ->searchable(),
                TextColumn::make('lifecycle_stage')
                    ->searchable(),
                TextColumn::make('owner_user_id')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('source')
                    ->searchable(),
                TextColumn::make('country_code')
                    ->searchable(),
                TextColumn::make('area_id')
                    ->searchable(),
                TextColumn::make('email')
                    ->label('Email address')
                    ->searchable(),
                TextColumn::make('phone')
                    ->searchable(),
                TextColumn::make('registered_address')
                    ->searchable(),
                TextColumn::make('website')
                    ->searchable(),
                TextColumn::make('industry')
                    ->searchable(),
                TextColumn::make('company_size')
                    ->searchable(),
                TextColumn::make('annual_revenue')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('pool_entered_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('last_activity_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('next_activity_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('first_order_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('last_order_at')
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
            'index' => ManageCustomers::route('/'),
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

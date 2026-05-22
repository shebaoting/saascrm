<?php

namespace App\Filament\Platform\Clusters\SubscriptionBilling\Resources\Plans;

use App\Filament\Platform\Clusters\SubscriptionBilling\Resources\Plans\Pages\ManagePlans;
use App\Filament\Platform\Clusters\SubscriptionBilling\SubscriptionBillingCluster;
use App\Models\Plan;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PlanResource extends Resource
{
    protected static ?string $model = Plan::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $navigationLabel = '套餐';

    protected static ?string $modelLabel = '套餐';

    protected static ?string $pluralModelLabel = '套餐';

    protected static ?string $title = '套餐';

    protected static bool $hasTitleCaseModelLabel = false;

    protected static ?string $cluster = SubscriptionBillingCluster::class;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                TextInput::make('code')
                    ->required(),
                TextInput::make('price_monthly')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('price_yearly')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('max_users')
                    ->numeric(),
                TextInput::make('max_leads')
                    ->numeric(),
                TextInput::make('max_customers')
                    ->numeric(),
                TextInput::make('max_storage_mb')
                    ->numeric(),
                TextInput::make('max_custom_fields')
                    ->numeric(),
                TextInput::make('max_automation_rules')
                    ->numeric(),
                TextInput::make('features'),
                Toggle::make('is_active')
                    ->required(),
            ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('name'),
                TextEntry::make('code'),
                TextEntry::make('price_monthly')
                    ->numeric(),
                TextEntry::make('price_yearly')
                    ->numeric(),
                TextEntry::make('max_users')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('max_leads')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('max_customers')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('max_storage_mb')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('max_custom_fields')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('max_automation_rules')
                    ->numeric()
                    ->placeholder('-'),
                IconEntry::make('is_active')
                    ->boolean(),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('code')
                    ->searchable(),
                TextColumn::make('price_monthly')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('price_yearly')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('max_users')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('max_leads')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('max_customers')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('max_storage_mb')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('max_custom_fields')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('max_automation_rules')
                    ->numeric()
                    ->sortable(),
                IconColumn::make('is_active')
                    ->boolean(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
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
            'index' => ManagePlans::route('/'),
        ];
    }
}

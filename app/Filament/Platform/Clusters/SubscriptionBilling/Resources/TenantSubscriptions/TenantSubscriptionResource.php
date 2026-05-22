<?php

namespace App\Filament\Platform\Clusters\SubscriptionBilling\Resources\TenantSubscriptions;

use App\Filament\Platform\Clusters\SubscriptionBilling\Resources\TenantSubscriptions\Pages\ManageTenantSubscriptions;
use App\Filament\Platform\Clusters\SubscriptionBilling\SubscriptionBillingCluster;
use App\Models\TenantSubscription;
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

class TenantSubscriptionResource extends Resource
{
    protected static ?string $model = TenantSubscription::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $navigationLabel = '订阅';

    protected static ?string $modelLabel = '订阅';

    protected static ?string $pluralModelLabel = '订阅';

    protected static ?string $title = '订阅';

    protected static bool $hasTitleCaseModelLabel = false;

    protected static ?string $cluster = SubscriptionBillingCluster::class;

    protected static ?string $recordTitleAttribute = 'status';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('tenant_id')
                    ->relationship('tenant', 'name')
                    ->required(),
                Select::make('plan_id')
                    ->relationship('plan', 'name')
                    ->required(),
                Select::make('status')
                    ->options(CrmUi::options('subscription.status'))
                    ->required()
                    ->default('trialing'),
                Select::make('billing_cycle')
                    ->options(CrmUi::options('subscription.billing_cycle'))
                    ->required()
                    ->default('manual'),
                DateTimePicker::make('starts_at')
                    ->required(),
                DateTimePicker::make('ends_at'),
                DateTimePicker::make('cancelled_at'),
                TextInput::make('metadata'),
            ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('tenant.name')
                    ->label('租户'),
                TextEntry::make('plan.name')
                    ->label('套餐'),
                TextEntry::make('status'),
                TextEntry::make('billing_cycle'),
                TextEntry::make('starts_at')
                    ->dateTime(),
                TextEntry::make('ends_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('cancelled_at')
                    ->dateTime()
                    ->placeholder('-'),
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
            ->recordTitleAttribute('status')
            ->columns([
                TextColumn::make('tenant.name')
                    ->searchable(),
                TextColumn::make('plan.name')
                    ->searchable(),
                TextColumn::make('status')
                    ->searchable(),
                TextColumn::make('billing_cycle')
                    ->searchable(),
                TextColumn::make('starts_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('ends_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('cancelled_at')
                    ->dateTime()
                    ->sortable(),
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
            'index' => ManageTenantSubscriptions::route('/'),
        ];
    }
}

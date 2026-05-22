<?php

namespace App\Filament\Clusters\SalesProcess\Resources\SalesTargets;

use App\Filament\Clusters\SalesProcess\Resources\SalesTargets\Pages\ManageSalesTargets;
use App\Filament\Clusters\SalesProcess\SalesProcessCluster;
use App\Models\SalesTarget;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SalesTargetResource extends Resource
{
    protected static ?string $model = SalesTarget::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $navigationLabel = '销售目标';

    protected static ?string $modelLabel = '销售目标';

    protected static ?string $pluralModelLabel = '销售目标';

    protected static ?string $title = '销售目标';

    protected static bool $hasTitleCaseModelLabel = false;

    protected static ?string $cluster = SalesProcessCluster::class;

    protected static ?string $recordTitleAttribute = 'target_type';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('target_type')
                    ->required()
                    ->default('user'),
                TextInput::make('target_id')
                    ->numeric(),
                TextInput::make('period_type')
                    ->required()
                    ->default('month'),
                DatePicker::make('period_start')
                    ->required(),
                DatePicker::make('period_end')
                    ->required(),
                TextInput::make('target_amount')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('target_payment_amount')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('target_customer_count')
                    ->required()
                    ->numeric()
                    ->default(0),
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
                TextEntry::make('target_type'),
                TextEntry::make('target_id')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('period_type'),
                TextEntry::make('period_start')
                    ->date(),
                TextEntry::make('period_end')
                    ->date(),
                TextEntry::make('target_amount')
                    ->numeric(),
                TextEntry::make('target_payment_amount')
                    ->numeric(),
                TextEntry::make('target_customer_count')
                    ->numeric(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('target_type')
            ->columns([
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('target_type')
                    ->searchable(),
                TextColumn::make('target_id')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('period_type')
                    ->searchable(),
                TextColumn::make('period_start')
                    ->date()
                    ->sortable(),
                TextColumn::make('period_end')
                    ->date()
                    ->sortable(),
                TextColumn::make('target_amount')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('target_payment_amount')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('target_customer_count')
                    ->numeric()
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
            'index' => ManageSalesTargets::route('/'),
        ];
    }
}

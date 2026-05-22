<?php

namespace App\Filament\Clusters\CustomerCenter\Resources\CustomerPoolRules;

use App\Filament\Clusters\CustomerCenter\CustomerCenterCluster;
use App\Filament\Clusters\CustomerCenter\Resources\CustomerPoolRules\Pages\ManageCustomerPoolRules;
use App\Models\CustomerPoolRule;
use App\Models\Department;
use App\Support\Filament\CrmUi;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
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

class CustomerPoolRuleResource extends Resource
{
    protected static ?string $model = CustomerPoolRule::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $navigationLabel = '公海规则';

    protected static ?string $modelLabel = '公海规则';

    protected static ?string $pluralModelLabel = '公海规则';

    protected static ?string $title = '公海规则';

    protected static bool $hasTitleCaseModelLabel = false;

    protected static ?string $cluster = CustomerCenterCluster::class;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('target_type')
                    ->options(CrmUi::options('target_type'))
                    ->required(),
                TextInput::make('name')
                    ->required(),
                TextInput::make('inactive_days')
                    ->required()
                    ->numeric()
                    ->default(30),
                TextInput::make('protect_days')
                    ->required()
                    ->numeric()
                    ->default(7),
                TextInput::make('max_claim_daily')
                    ->numeric(),
                Select::make('department_ids')
                    ->multiple()
                    ->options(fn (): array => Department::query()->orderBy('name')->pluck('name', 'id')->all()),
                Toggle::make('is_active')
                    ->required(),
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
                TextEntry::make('name'),
                TextEntry::make('inactive_days')
                    ->numeric(),
                TextEntry::make('protect_days')
                    ->numeric(),
                TextEntry::make('max_claim_daily')
                    ->numeric()
                    ->placeholder('-'),
                IconEntry::make('is_active')
                    ->boolean(),
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
                TextColumn::make('target_type')
                    ->searchable(),
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('inactive_days')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('protect_days')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('max_claim_daily')
                    ->numeric()
                    ->sortable(),
                IconColumn::make('is_active')
                    ->boolean(),
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
            'index' => ManageCustomerPoolRules::route('/'),
        ];
    }
}

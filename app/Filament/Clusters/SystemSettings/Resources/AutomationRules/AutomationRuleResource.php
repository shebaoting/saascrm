<?php

namespace App\Filament\Clusters\SystemSettings\Resources\AutomationRules;

use App\Filament\Clusters\SystemSettings\Resources\AutomationRules\Pages\ManageAutomationRules;
use App\Filament\Clusters\SystemSettings\SystemSettingsCluster;
use App\Models\AutomationRule;
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
use Filament\Forms\Components\Toggle;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AutomationRuleResource extends Resource
{
    protected static ?string $model = AutomationRule::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $navigationLabel = '自动化规则';

    protected static ?string $modelLabel = '自动化规则';

    protected static ?string $pluralModelLabel = '自动化规则';

    protected static ?string $title = '自动化规则';

    protected static bool $hasTitleCaseModelLabel = false;

    protected static ?string $cluster = SystemSettingsCluster::class;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                Select::make('trigger_type')
                    ->options(CrmUi::options('automation.trigger_type'))
                    ->required(),
                Select::make('target_type')
                    ->options(CrmUi::options('target_type'))
                    ->required(),
                TextInput::make('conditions'),
                Toggle::make('is_active')
                    ->required(),
                DateTimePicker::make('last_run_at'),
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
                TextEntry::make('name'),
                TextEntry::make('trigger_type'),
                TextEntry::make('target_type'),
                IconEntry::make('is_active')
                    ->boolean(),
                TextEntry::make('last_run_at')
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
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('trigger_type')
                    ->searchable(),
                TextColumn::make('target_type')
                    ->searchable(),
                IconColumn::make('is_active')
                    ->boolean(),
                TextColumn::make('last_run_at')
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
            'index' => ManageAutomationRules::route('/'),
        ];
    }
}

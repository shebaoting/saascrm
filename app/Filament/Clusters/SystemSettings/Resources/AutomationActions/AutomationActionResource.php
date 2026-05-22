<?php

namespace App\Filament\Clusters\SystemSettings\Resources\AutomationActions;

use App\Filament\Clusters\SystemSettings\Resources\AutomationActions\Pages\ManageAutomationActions;
use App\Filament\Clusters\SystemSettings\SystemSettingsCluster;
use App\Filament\Concerns\UsesCrmAccess;
use App\Models\AutomationAction;
use App\Support\Filament\CrmUi;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AutomationActionResource extends Resource
{
    use UsesCrmAccess;

    protected static ?string $model = AutomationAction::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $navigationLabel = '自动化动作';

    protected static ?string $modelLabel = '自动化动作';

    protected static ?string $pluralModelLabel = '自动化动作';

    protected static ?string $title = '自动化动作';

    protected static bool $hasTitleCaseModelLabel = false;

    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $cluster = SystemSettingsCluster::class;

    protected static ?string $recordTitleAttribute = 'action_type';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('automation_rule_id')
                    ->relationship('rule', 'name')
                    ->required(),
                Select::make('action_type')
                    ->options(CrmUi::options('automation.action_type'))
                    ->required(),
                Textarea::make('payload')
                    ->formatStateUsing(fn ($state): ?string => is_array($state) ? json_encode($state, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) : $state)
                    ->dehydrateStateUsing(function (?string $state): ?array {
                        $decoded = blank($state) ? null : json_decode($state, true);

                        return is_array($decoded) ? $decoded : null;
                    }),
                TextInput::make('sort_order')
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
                TextEntry::make('rule.name'),
                TextEntry::make('action_type'),
                TextEntry::make('sort_order')
                    ->numeric(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('action_type')
            ->columns([
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('rule.name')
                    ->searchable(),
                TextColumn::make('action_type')
                    ->searchable(),
                TextColumn::make('sort_order')
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
            'index' => ManageAutomationActions::route('/'),
        ];
    }
}

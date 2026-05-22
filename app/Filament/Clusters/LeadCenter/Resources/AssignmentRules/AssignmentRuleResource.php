<?php

namespace App\Filament\Clusters\LeadCenter\Resources\AssignmentRules;

use App\Filament\Clusters\LeadCenter\LeadCenterCluster;
use App\Filament\Clusters\LeadCenter\Resources\AssignmentRules\Pages\ManageAssignmentRules;
use App\Filament\Concerns\UsesCrmAccess;
use App\Models\AssignmentRule;
use App\Models\User;
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

class AssignmentRuleResource extends Resource
{
    use UsesCrmAccess;

    protected static ?string $model = AssignmentRule::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $navigationLabel = '分配规则';

    protected static ?string $modelLabel = '分配规则';

    protected static ?string $pluralModelLabel = '分配规则';

    protected static ?string $title = '分配规则';

    protected static bool $hasTitleCaseModelLabel = false;

    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $cluster = LeadCenterCluster::class;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                Select::make('target_type')
                    ->options(CrmUi::options('target_type'))
                    ->required()
                    ->default('lead'),
                Select::make('method')
                    ->options(CrmUi::options('assignment.method'))
                    ->required()
                    ->default('round_robin'),
                Select::make('department_id')
                    ->relationship('department', 'name'),
                Select::make('user_ids')
                    ->multiple()
                    ->options(fn (): array => User::query()->orderBy('name')->pluck('name', 'id')->all()),
                TextInput::make('max_per_user_daily')
                    ->numeric(),
                TextInput::make('priority')
                    ->required()
                    ->numeric()
                    ->default(0),
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
                TextEntry::make('name'),
                TextEntry::make('target_type'),
                TextEntry::make('method'),
                TextEntry::make('department.name')
                    ->placeholder('-'),
                TextEntry::make('max_per_user_daily')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('priority')
                    ->numeric(),
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
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('target_type')
                    ->searchable(),
                TextColumn::make('method')
                    ->searchable(),
                TextColumn::make('department.name')
                    ->searchable(),
                TextColumn::make('max_per_user_daily')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('priority')
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
            'index' => ManageAssignmentRules::route('/'),
        ];
    }
}

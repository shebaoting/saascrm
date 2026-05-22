<?php

namespace App\Filament\Clusters\SalesProcess\Resources\PipelineStages;

use App\Filament\Clusters\SalesProcess\Resources\PipelineStages\Pages\ManagePipelineStages;
use App\Filament\Clusters\SalesProcess\SalesProcessCluster;
use App\Filament\Concerns\UsesCrmAccess;
use App\Models\PipelineStage;
use App\Support\Filament\CustomFieldUi;
use App\Support\Filament\CrmUi;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\CheckboxList;
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

class PipelineStageResource extends Resource
{
    use UsesCrmAccess;

    protected static ?string $model = PipelineStage::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $navigationLabel = '销售阶段';

    protected static ?string $modelLabel = '销售阶段';

    protected static ?string $pluralModelLabel = '销售阶段';

    protected static ?string $title = '销售阶段';

    protected static bool $hasTitleCaseModelLabel = false;

    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $cluster = SalesProcessCluster::class;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('pipeline_id')
                    ->relationship('pipeline', 'name')
                    ->required(),
                TextInput::make('name')
                    ->required(),
                TextInput::make('probability')
                    ->required()
                    ->numeric()
                    ->default(0),
                Select::make('stage_type')
                    ->options(CrmUi::options('pipeline.stage_type'))
                    ->required()
                    ->default('open'),
                CheckboxList::make('required_fields')
                    ->options(fn (): array => CustomFieldUi::requiredFieldOptions('opportunity'))
                    ->columns(2)
                    ->bulkToggleable()
                    ->columnSpanFull(),
                TextInput::make('sort_order')
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
                TextEntry::make('pipeline.name')
                    ->label('销售管道'),
                TextEntry::make('name'),
                TextEntry::make('probability')
                    ->numeric(),
                TextEntry::make('stage_type'),
                TextEntry::make('sort_order')
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
                TextColumn::make('pipeline.name')
                    ->searchable(),
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('probability')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('stage_type')
                    ->searchable(),
                TextColumn::make('sort_order')
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
            'index' => ManagePipelineStages::route('/'),
        ];
    }
}

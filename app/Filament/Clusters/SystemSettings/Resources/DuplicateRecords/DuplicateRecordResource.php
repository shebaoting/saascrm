<?php

namespace App\Filament\Clusters\SystemSettings\Resources\DuplicateRecords;

use App\Filament\Clusters\SystemSettings\Resources\DuplicateRecords\Pages\ManageDuplicateRecords;
use App\Filament\Clusters\SystemSettings\SystemSettingsCluster;
use App\Filament\Concerns\UsesCrmAccess;
use App\Models\DuplicateRecord;
use App\Services\Crm\DuplicateDetectionService;
use App\Support\Filament\CrmUi;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class DuplicateRecordResource extends Resource
{
    use UsesCrmAccess;

    protected static ?string $model = DuplicateRecord::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedExclamationTriangle;

    protected static ?string $navigationLabel = '疑似重复池';

    protected static ?string $modelLabel = '疑似重复';

    protected static ?string $pluralModelLabel = '疑似重复池';

    protected static ?string $title = '疑似重复池';

    protected static bool $hasTitleCaseModelLabel = false;

    protected static ?string $cluster = SystemSettingsCluster::class;

    protected static ?string $recordTitleAttribute = 'field_value';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('target_type'),
                TextEntry::make('target_id')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('matched_type'),
                TextEntry::make('matched_id')
                    ->numeric(),
                TextEntry::make('field_name'),
                TextEntry::make('field_value'),
                TextEntry::make('status'),
                TextEntry::make('payload')
                    ->formatStateUsing(fn (mixed $state): string => json_encode($state, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT))
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('field_value')
            ->columns([
                TextColumn::make('target_type')
                    ->searchable(),
                TextColumn::make('field_name')
                    ->searchable(),
                TextColumn::make('field_value')
                    ->searchable(),
                TextColumn::make('matched_type')
                    ->searchable(),
                TextColumn::make('matched_id')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('status')
                    ->searchable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options(CrmUi::options('duplicate.status')),
                SelectFilter::make('target_type')
                    ->options([
                        'lead' => '线索',
                        'customer' => '客户',
                    ]),
            ])
            ->recordActions([
                Action::make('ignore')
                    ->label('忽略')
                    ->icon('heroicon-o-eye-slash')
                    ->visible(fn (DuplicateRecord $record): bool => $record->status === 'pending')
                    ->requiresConfirmation()
                    ->action(function (DuplicateRecord $record): void {
                        app(DuplicateDetectionService::class)->ignore($record);

                        Notification::make()->success()->title('已忽略该疑似重复')->send();
                    }),
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
            'index' => ManageDuplicateRecords::route('/'),
        ];
    }
}

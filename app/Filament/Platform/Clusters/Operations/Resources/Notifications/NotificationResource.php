<?php

namespace App\Filament\Platform\Clusters\Operations\Resources\Notifications;

use App\Filament\Platform\Clusters\Operations\OperationsCluster;
use App\Filament\Platform\Clusters\Operations\Resources\Notifications\Pages\ManageNotifications;
use App\Models\Notification;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class NotificationResource extends Resource
{
    protected static ?string $model = Notification::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $navigationLabel = '通知';

    protected static ?string $modelLabel = '通知';

    protected static ?string $pluralModelLabel = '通知';

    protected static ?string $title = '通知';

    protected static bool $hasTitleCaseModelLabel = false;

    protected static ?string $cluster = OperationsCluster::class;

    protected static ?string $recordTitleAttribute = 'type';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('tenant_id')
                    ->numeric(),
                TextInput::make('type')
                    ->required(),
                TextInput::make('notifiable_type')
                    ->required(),
                TextInput::make('notifiable_id')
                    ->required()
                    ->numeric(),
                Textarea::make('data')
                    ->required()
                    ->columnSpanFull(),
                DateTimePicker::make('read_at'),
            ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('id')
                    ->label('编号'),
                TextEntry::make('tenant_id')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('type'),
                TextEntry::make('notifiable_type'),
                TextEntry::make('notifiable_id')
                    ->numeric(),
                TextEntry::make('data')
                    ->columnSpanFull(),
                TextEntry::make('read_at')
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
            ->recordTitleAttribute('type')
            ->columns([
                TextColumn::make('id')
                    ->label('编号'),
                TextColumn::make('tenant_id')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('type')
                    ->searchable(),
                TextColumn::make('notifiable_type')
                    ->searchable(),
                TextColumn::make('notifiable_id')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('read_at')
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
            'index' => ManageNotifications::route('/'),
        ];
    }
}

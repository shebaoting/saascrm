<?php

namespace App\Filament\Clusters\SystemSettings\Resources\TenantInvitations;

use App\Filament\Clusters\SystemSettings\Resources\TenantInvitations\Pages\ManageTenantInvitations;
use App\Filament\Clusters\SystemSettings\SystemSettingsCluster;
use App\Models\TenantInvitation;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class TenantInvitationResource extends Resource
{
    protected static ?string $model = TenantInvitation::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $navigationLabel = '成员邀请';

    protected static ?string $modelLabel = '成员邀请';

    protected static ?string $pluralModelLabel = '成员邀请';

    protected static ?string $title = '成员邀请';

    protected static bool $hasTitleCaseModelLabel = false;

    protected static ?string $cluster = SystemSettingsCluster::class;

    protected static ?string $recordTitleAttribute = 'email';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('email')
                    ->label('Email address')
                    ->email(),
                TextInput::make('phone')
                    ->tel(),
                TextInput::make('role_ids'),
                TextInput::make('department_ids'),
                TextInput::make('token')
                    ->required(),
                TextInput::make('status')
                    ->required()
                    ->default('pending'),
                TextInput::make('invited_by')
                    ->required()
                    ->numeric(),
                DateTimePicker::make('accepted_at'),
                DateTimePicker::make('expires_at')
                    ->required(),
            ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('email')
                    ->label('Email address')
                    ->placeholder('-'),
                TextEntry::make('phone')
                    ->placeholder('-'),
                TextEntry::make('token'),
                TextEntry::make('status'),
                TextEntry::make('invited_by')
                    ->numeric(),
                TextEntry::make('accepted_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('expires_at')
                    ->dateTime(),
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
            ->recordTitleAttribute('email')
            ->columns([
                TextColumn::make('email')
                    ->label('Email address')
                    ->searchable(),
                TextColumn::make('phone')
                    ->searchable(),
                TextColumn::make('token')
                    ->searchable(),
                TextColumn::make('status')
                    ->searchable(),
                TextColumn::make('invited_by')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('accepted_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('expires_at')
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
            'index' => ManageTenantInvitations::route('/'),
        ];
    }
}

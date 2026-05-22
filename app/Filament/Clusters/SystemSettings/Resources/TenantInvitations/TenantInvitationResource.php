<?php

namespace App\Filament\Clusters\SystemSettings\Resources\TenantInvitations;

use App\Filament\Clusters\SystemSettings\Resources\TenantInvitations\Pages\ManageTenantInvitations;
use App\Filament\Clusters\SystemSettings\SystemSettingsCluster;
use App\Filament\Concerns\UsesCrmAccess;
use App\Models\Department;
use App\Models\Role;
use App\Models\TenantInvitation;
use App\Support\CrmAccess;
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
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class TenantInvitationResource extends Resource
{
    use UsesCrmAccess;

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
                    ->label('邮箱')
                    ->email(),
                TextInput::make('phone')
                    ->tel(),
                Select::make('role_ids')
                    ->multiple()
                    ->options(fn (): array => Role::query()
                        ->where('tenant_id', CrmAccess::tenantId())
                        ->orderBy('name')
                        ->pluck('name', 'id')
                        ->all()),
                Select::make('department_ids')
                    ->multiple()
                    ->options(fn (): array => Department::query()
                        ->where('tenant_id', CrmAccess::tenantId())
                        ->orderBy('name')
                        ->pluck('name', 'id')
                        ->all()),
                TextInput::make('token')
                    ->disabled()
                    ->dehydrated(false)
                    ->placeholder('保存后自动生成'),
                Select::make('status')
                    ->options([
                        'pending' => '待接受',
                        'accepted' => '已接受',
                        'expired' => '已过期',
                        'cancelled' => '已取消',
                    ])
                    ->required()
                    ->default('pending'),
                Select::make('invited_by')
                    ->relationship('inviter', 'name')
                    ->default(fn (): ?int => auth()->id())
                    ->required(),
                DateTimePicker::make('accepted_at'),
                DateTimePicker::make('expires_at')
                    ->default(fn () => now()->addDays(7)),
            ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('email')
                    ->label('邮箱')
                    ->placeholder('-'),
                TextEntry::make('phone')
                    ->placeholder('-'),
                TextEntry::make('token'),
                TextEntry::make('invite_url')
                    ->label('邀请链接')
                    ->state(fn (TenantInvitation $record): string => route('tenant-invitations.show', $record->token))
                    ->columnSpanFull(),
                TextEntry::make('status'),
                TextEntry::make('inviter.name'),
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
                    ->label('邮箱')
                    ->searchable(),
                TextColumn::make('phone')
                    ->searchable(),
                TextColumn::make('token')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('invite_url')
                    ->label('邀请链接')
                    ->state(fn (TenantInvitation $record): string => route('tenant-invitations.show', $record->token))
                    ->copyable()
                    ->limit(36),
                TextColumn::make('status')
                    ->searchable(),
                TextColumn::make('inviter.name')
                    ->searchable(),
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
                SelectFilter::make('status')
                    ->options([
                        'pending' => '待接受',
                        'accepted' => '已接受',
                        'expired' => '已过期',
                        'cancelled' => '已取消',
                    ]),
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

<?php

namespace App\Filament\Clusters\SystemSettings\Resources\TenantInvitations;

use App\Filament\Clusters\SystemSettings\Resources\TenantInvitations\Pages\CreateTenantInvitation;
use App\Filament\Clusters\SystemSettings\Resources\TenantInvitations\Pages\EditTenantInvitation;
use App\Filament\Clusters\SystemSettings\Resources\TenantInvitations\Pages\ListTenantInvitations;
use App\Filament\Clusters\SystemSettings\Resources\TenantInvitations\Pages\ViewTenantInvitation;
use App\Filament\Clusters\SystemSettings\Resources\TenantInvitations\Schemas\TenantInvitationForm;
use App\Filament\Clusters\SystemSettings\Resources\TenantInvitations\Schemas\TenantInvitationInfolist;
use App\Filament\Clusters\SystemSettings\Resources\TenantInvitations\Tables\TenantInvitationTable;
use App\Filament\Clusters\SystemSettings\SystemSettingsCluster;
use App\Filament\Concerns\UsesCrmAccess;
use App\Models\TenantInvitation;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
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
        return TenantInvitationForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return TenantInvitationInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TenantInvitationTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTenantInvitations::route('/'),
            'create' => CreateTenantInvitation::route('/create'),
            'view' => ViewTenantInvitation::route('/{record}'),
            'edit' => EditTenantInvitation::route('/{record}/edit'),
        ];
    }
}

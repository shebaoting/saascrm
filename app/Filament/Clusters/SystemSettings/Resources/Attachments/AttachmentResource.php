<?php

namespace App\Filament\Clusters\SystemSettings\Resources\Attachments;

use App\Filament\Clusters\SystemSettings\Resources\Attachments\Pages\CreateAttachment;
use App\Filament\Clusters\SystemSettings\Resources\Attachments\Pages\EditAttachment;
use App\Filament\Clusters\SystemSettings\Resources\Attachments\Pages\ListAttachments;
use App\Filament\Clusters\SystemSettings\Resources\Attachments\Pages\ViewAttachment;
use App\Filament\Clusters\SystemSettings\Resources\Attachments\Schemas\AttachmentForm;
use App\Filament\Clusters\SystemSettings\Resources\Attachments\Schemas\AttachmentInfolist;
use App\Filament\Clusters\SystemSettings\Resources\Attachments\Tables\AttachmentTable;
use App\Filament\Clusters\SystemSettings\SystemSettingsCluster;
use App\Filament\Concerns\UsesCrmAccess;
use App\Models\Attachment;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class AttachmentResource extends Resource
{
    use UsesCrmAccess;

    protected static ?string $model = Attachment::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $navigationLabel = '附件';

    protected static ?string $modelLabel = '附件';

    protected static ?string $pluralModelLabel = '附件';

    protected static ?string $title = '附件';

    protected static bool $hasTitleCaseModelLabel = false;

    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $cluster = SystemSettingsCluster::class;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return AttachmentForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return AttachmentInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AttachmentTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAttachments::route('/'),
            'create' => CreateAttachment::route('/create'),
            'view' => ViewAttachment::route('/{record}'),
            'edit' => EditAttachment::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}

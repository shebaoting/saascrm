<?php

namespace App\Filament\Clusters\CustomerCenter\Resources\Contacts;

use App\Filament\Clusters\CustomerCenter\CustomerCenterCluster;
use App\Filament\Clusters\CustomerCenter\Resources\Contacts\Pages\CreateContact;
use App\Filament\Clusters\CustomerCenter\Resources\Contacts\Pages\EditContact;
use App\Filament\Clusters\CustomerCenter\Resources\Contacts\Pages\ListContacts;
use App\Filament\Clusters\CustomerCenter\Resources\Contacts\Pages\ViewContact;
use App\Filament\Clusters\CustomerCenter\Resources\Contacts\Schemas\ContactForm;
use App\Filament\Clusters\CustomerCenter\Resources\Contacts\Schemas\ContactInfolist;
use App\Filament\Clusters\CustomerCenter\Resources\Contacts\Tables\ContactTable;
use App\Filament\Concerns\UsesCrmAccess;
use App\Models\Contact;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ContactResource extends Resource
{
    use UsesCrmAccess;

    protected static ?string $model = Contact::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $navigationLabel = '联系人';

    protected static ?string $modelLabel = '联系人';

    protected static ?string $pluralModelLabel = '联系人';

    protected static ?string $title = '联系人';

    protected static bool $hasTitleCaseModelLabel = false;

    protected static ?string $cluster = CustomerCenterCluster::class;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return ContactForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ContactInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ContactTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListContacts::route('/'),
            'create' => CreateContact::route('/create'),
            'view' => ViewContact::route('/{record}'),
            'edit' => EditContact::route('/{record}/edit'),
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

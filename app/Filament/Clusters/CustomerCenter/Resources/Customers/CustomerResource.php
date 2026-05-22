<?php

namespace App\Filament\Clusters\CustomerCenter\Resources\Customers;

use App\Filament\Clusters\CustomerCenter\CustomerCenterCluster;
use App\Filament\Clusters\CustomerCenter\Resources\Customers\Pages\CreateCustomer;
use App\Filament\Clusters\CustomerCenter\Resources\Customers\Pages\EditCustomer;
use App\Filament\Clusters\CustomerCenter\Resources\Customers\Pages\ListCustomers;
use App\Filament\Clusters\CustomerCenter\Resources\Customers\Pages\ViewCustomer;
use App\Filament\Clusters\CustomerCenter\Resources\Customers\Schemas\CustomerForm;
use App\Filament\Clusters\CustomerCenter\Resources\Customers\Schemas\CustomerInfolist;
use App\Filament\Clusters\CustomerCenter\Resources\Customers\Tables\CustomerTable;
use App\Filament\Concerns\UsesCrmAccess;
use App\Models\Customer;
use BackedEnum;
use Filament\Actions\CreateAction;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Width;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CustomerResource extends Resource
{
    use UsesCrmAccess;

    protected static ?string $model = Customer::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $navigationLabel = '全部客户';

    protected static ?string $modelLabel = '全部客户';

    protected static ?string $pluralModelLabel = '全部客户';

    protected static ?string $title = '全部客户';

    protected static bool $hasTitleCaseModelLabel = false;

    protected static ?string $cluster = CustomerCenterCluster::class;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return CustomerForm::configure($schema);
    }

    public static function makeCreateAction(): CreateAction
    {
        return CreateAction::make()
            ->label('添加客户')
            ->modalHeading('添加客户')
            ->modalSubmitActionLabel('添加')
            ->modalWidth(Width::ThreeExtraLarge)
            ->stickyModalFooter()
            ->createAnother(false)
            ->mutateDataUsing(function (array $data): array {
                $data['owner_user_id'] = auth()->id();

                return $data;
            })
            ->after(function (Customer $record): void {
                $contact = $record->contacts()
                    ->oldest('id')
                    ->first();

                if ($contact) {
                    $contact->forceFill(['is_primary' => true])->save();
                }
            })
            ->successNotificationTitle('客户已添加');
    }

    public static function infolist(Schema $schema): Schema
    {
        return CustomerInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CustomerTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCustomers::route('/'),
            'create' => CreateCustomer::route('/create'),
            'view' => ViewCustomer::route('/{record}'),
            'edit' => EditCustomer::route('/{record}/edit'),
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

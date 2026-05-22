<?php

namespace App\Filament\Clusters\CustomerCenter\Resources\MyCustomers\Pages;

use App\Filament\Clusters\CustomerCenter\Resources\MyCustomers\MyCustomerResource;
use App\Models\Customer;
use Filament\Resources\Pages\CreateRecord;

class CreateMyCustomer extends CreateRecord
{
    protected static string $resource = MyCustomerResource::class;

    protected static bool $canCreateAnother = false;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['owner_user_id'] = auth()->id();

        return $data;
    }

    protected function afterCreate(): void
    {
        if (! $this->record instanceof Customer) {
            return;
        }

        $contact = $this->record->contacts()
            ->oldest('id')
            ->first();

        if ($contact) {
            $contact->forceFill(['is_primary' => true])->save();
        }
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return '客户已添加';
    }
}

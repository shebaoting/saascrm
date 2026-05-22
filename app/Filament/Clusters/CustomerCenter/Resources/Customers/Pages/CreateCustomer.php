<?php

namespace App\Filament\Clusters\CustomerCenter\Resources\Customers\Pages;

use App\Filament\Clusters\CustomerCenter\Resources\Customers\CustomerResource;
use App\Models\Customer;
use Filament\Resources\Pages\CreateRecord;

class CreateCustomer extends CreateRecord
{
    protected static string $resource = CustomerResource::class;

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

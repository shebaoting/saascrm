<?php

namespace App\Services\Crm;

use App\Models\Customer;
use App\Models\MergeHistory;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CustomerMergeService
{
    public function merge(Customer $source, Customer $target): Customer
    {
        if ($source->tenant_id !== $target->tenant_id || $source->is($target)) {
            throw ValidationException::withMessages([
                'target_customer_id' => '请选择同一租户下的另一个客户。',
            ]);
        }

        return DB::transaction(function () use ($source, $target): Customer {
            $changedFields = [];

            foreach (['phone', 'email', 'website', 'industry', 'company_size', 'registered_address'] as $field) {
                if (blank($target->{$field}) && filled($source->{$field})) {
                    $target->{$field} = $source->{$field};
                    $changedFields[$field] = $source->{$field};
                }
            }

            $target->save();

            $source->contacts()->update(['customer_id' => $target->id]);
            $source->activities()->update(['customer_id' => $target->id]);
            $source->tasks()->update(['customer_id' => $target->id]);
            $source->opportunities()->update(['customer_id' => $target->id]);
            $source->quotes()->update(['customer_id' => $target->id]);
            $source->orders()->update(['customer_id' => $target->id]);

            MergeHistory::create([
                'tenant_id' => $source->tenant_id,
                'model_type' => Customer::class,
                'source_id' => $source->id,
                'target_id' => $target->id,
                'merged_fields' => $changedFields,
                'merged_relations' => ['contacts', 'activities', 'tasks', 'opportunities', 'quotes', 'orders'],
                'merged_by' => Auth::id(),
                'created_at' => now(),
            ]);

            $source->delete();

            return $target->refresh();
        });
    }
}

<?php

namespace App\Services\Crm;

use App\Models\Customer;
use App\Models\MergeHistory;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CustomerMergeService
{
    /**
     * @return array<string, mixed>
     */
    public function preview(Customer $source, Customer $target): array
    {
        $this->assertCanMerge($source, $target);

        $fields = collect($this->mergeableFields())
            ->mapWithKeys(fn (string $field): array => [
                $field => [
                    'source' => $source->{$field},
                    'target' => $target->{$field},
                    'recommended' => blank($target->{$field}) && filled($source->{$field}) ? 'source' : 'target',
                    'conflict' => filled($source->{$field}) && filled($target->{$field}) && $source->{$field} !== $target->{$field},
                ],
            ])
            ->all();

        return [
            'fields' => $fields,
            'relations' => [
                'contacts' => $source->contacts()->count(),
                'activities' => $source->activities()->count(),
                'tasks' => $source->tasks()->count(),
                'opportunities' => $source->opportunities()->count(),
                'quotes' => $source->quotes()->count(),
                'orders' => $source->orders()->count(),
            ],
        ];
    }

    public function merge(Customer $source, Customer $target): Customer
    {
        $choices = collect($this->preview($source, $target)['fields'])
            ->mapWithKeys(fn (array $meta, string $field): array => [$field => $meta['recommended']])
            ->all();

        return $this->mergeWithFields($source, $target, $choices);
    }

    /**
     * @param  array<string, string>  $fieldChoices
     */
    public function mergeWithFields(Customer $source, Customer $target, array $fieldChoices): Customer
    {
        $this->assertCanMerge($source, $target);

        return DB::transaction(function () use ($source, $target, $fieldChoices): Customer {
            $changedFields = [];

            foreach ($this->mergeableFields() as $field) {
                if (($fieldChoices[$field] ?? 'target') === 'source' && filled($source->{$field})) {
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

    private function assertCanMerge(Customer $source, Customer $target): void
    {
        if ($source->tenant_id !== $target->tenant_id || $source->is($target)) {
            throw ValidationException::withMessages([
                'target_customer_id' => '请选择同一租户下的另一个客户。',
            ]);
        }
    }

    /**
     * @return array<int, string>
     */
    private function mergeableFields(): array
    {
        return ['phone', 'email'];
    }
}

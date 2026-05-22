<?php

namespace App\Services\Crm;

use App\Models\Customer;
use App\Models\DuplicateRecord;
use App\Models\Lead;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class DuplicateDetectionService
{
    public function assertNoDuplicateOnCreate(Model $record): void
    {
        if (app()->bound('crm.skip_duplicate_detection') && app('crm.skip_duplicate_detection')) {
            return;
        }

        if (! $record instanceof Lead && ! $record instanceof Customer) {
            return;
        }

        $matches = $this->findMatches($record);

        if ($matches === []) {
            return;
        }

        foreach ($matches as $match) {
            DuplicateRecord::firstOrCreate([
                'tenant_id' => $record->tenant_id,
                'target_type' => $record instanceof Lead ? 'lead' : 'customer',
                'target_id' => null,
                'matched_type' => $match['matched_type'],
                'matched_id' => $match['matched_id'],
                'field_name' => $match['field_name'],
                'field_value' => $match['field_value'],
                'status' => 'pending',
            ], [
                'payload' => $record->getAttributes(),
            ]);
        }

        $fieldNames = collect($matches)
            ->map(fn (array $match): string => $this->fieldLabel($match['field_name']))
            ->unique()
            ->join('、');

        throw ValidationException::withMessages([
            'duplicate' => '发现疑似重复'.$fieldNames.'，已进入疑似重复池，请确认后再创建。',
        ]);
    }

    public function ignore(DuplicateRecord $record): DuplicateRecord
    {
        $record->forceFill([
            'status' => 'ignored',
            'resolved_by' => Auth::id(),
            'resolved_at' => now(),
        ])->save();

        return $record->refresh();
    }

    /**
     * @return array<int, array{matched_type: string, matched_id: int, field_name: string, field_value: string}>
     */
    public function findMatches(Lead|Customer $record): array
    {
        $tenantId = (int) $record->tenant_id;
        $candidates = $this->candidateValues($record);
        $matches = [];

        foreach ($candidates as $field => $value) {
            if (blank($value)) {
                continue;
            }

            foreach ($this->leadMatches($tenantId, $field, $value, $record) as $match) {
                $matches[] = $match;
            }

            foreach ($this->customerMatches($tenantId, $field, $value, $record) as $match) {
                $matches[] = $match;
            }
        }

        return collect($matches)
            ->unique(fn (array $match): string => implode(':', $match))
            ->values()
            ->all();
    }

    private function candidateValues(Lead|Customer $record): array
    {
        if ($record instanceof Lead) {
            return [
                'phone' => $this->phone($record->phone),
                'email' => $this->email($record->email),
                'company_name' => $this->name($record->company_name),
            ];
        }

        return [
            'phone' => $this->phone($record->phone),
            'email' => $this->email($record->email),
            'company_name' => $this->name($record->name),
        ];
    }

    private function leadMatches(int $tenantId, string $field, string $value, Lead|Customer $record): array
    {
        $column = $field === 'company_name' ? 'company_name' : $field;

        return Lead::query()
            ->where('tenant_id', $tenantId)
            ->when($record instanceof Lead && $record->exists, fn ($query) => $query->whereKeyNot($record->id))
            ->where($column, $value)
            ->limit(10)
            ->get(['id'])
            ->map(fn (Lead $lead): array => [
                'matched_type' => 'lead',
                'matched_id' => $lead->id,
                'field_name' => $field,
                'field_value' => $value,
            ])
            ->all();
    }

    private function customerMatches(int $tenantId, string $field, string $value, Lead|Customer $record): array
    {
        $column = $field === 'company_name' ? 'name' : $field;

        return Customer::query()
            ->where('tenant_id', $tenantId)
            ->when($record instanceof Customer && $record->exists, fn ($query) => $query->whereKeyNot($record->id))
            ->where($column, $value)
            ->limit(10)
            ->get(['id'])
            ->map(fn (Customer $customer): array => [
                'matched_type' => 'customer',
                'matched_id' => $customer->id,
                'field_name' => $field,
                'field_value' => $value,
            ])
            ->all();
    }

    private function phone(?string $value): ?string
    {
        return filled($value) ? preg_replace('/[^\d+]/', '', $value) : null;
    }

    private function email(?string $value): ?string
    {
        return filled($value) ? Str::lower(trim($value)) : null;
    }

    private function name(?string $value): ?string
    {
        if (blank($value)) {
            return null;
        }

        $value = preg_replace('/\s+/u', ' ', trim($value));

        return str_replace(['（', '）'], ['(', ')'], $value);
    }

    private function fieldLabel(string $field): string
    {
        return match ($field) {
            'phone' => '手机号',
            'email' => '邮箱',
            'company_name' => '客户名称',
            default => $field,
        };
    }
}

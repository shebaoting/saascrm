<?php

namespace App\Services\Crm;

use App\Models\Lead;
use App\Models\LeadScoreRule;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class LeadScoringService
{
    public function score(Lead $lead): int
    {
        return (int) $this->explain($lead)['total'];
    }

    public function refresh(Lead $lead): Lead
    {
        $lead->forceFill(['score' => $this->score($lead)])->save();

        return $lead->refresh();
    }

    public function refreshMany(int $tenantId): int
    {
        $count = 0;

        Lead::query()
            ->where('tenant_id', $tenantId)
            ->whereNull('deleted_at')
            ->chunkById(100, function (Collection $leads) use (&$count): void {
                $leads->each(function (Lead $lead) use (&$count): void {
                    $this->refresh($lead);
                    $count++;
                });
            });

        return $count;
    }

    /**
     * @return array{rules: array<int, array{name: string, score: int}>, completeness: array<int, array{name: string, score: int}>, behavior: array<int, array{name: string, score: int}>, total: int}
     */
    public function explain(Lead $lead): array
    {
        $rules = [];
        $completeness = $this->completenessItems($lead);
        $behavior = $this->behaviorItems($lead);

        LeadScoreRule::query()
            ->where('tenant_id', $lead->tenant_id)
            ->where('is_active', true)
            ->get()
            ->each(function (LeadScoreRule $rule) use ($lead, &$rules): void {
                if ($this->matches($lead, $rule)) {
                    $rules[] = [
                        'name' => $rule->name,
                        'score' => (int) $rule->score,
                    ];
                }
            });

        $total = collect($rules)
            ->merge($completeness)
            ->merge($behavior)
            ->sum('score');

        return [
            'rules' => $rules,
            'completeness' => $completeness,
            'behavior' => $behavior,
            'total' => max(0, (int) $total),
        ];
    }

    private function matches(Lead $lead, LeadScoreRule $rule): bool
    {
        $actual = $this->fieldValue($lead, $rule->field);
        $expected = $this->expectedValues($rule->value);
        $firstExpected = Arr::first($expected);

        return match ($rule->operator) {
            'eq' => (string) $actual === (string) $firstExpected,
            'neq' => (string) $actual !== (string) $firstExpected,
            'contains' => str_contains((string) $actual, (string) $firstExpected),
            'in' => in_array((string) $actual, array_map('strval', $expected), true),
            'not_in' => ! in_array((string) $actual, array_map('strval', $expected), true),
            'not_empty' => filled($actual),
            'empty' => blank($actual),
            'gt' => (float) $actual > (float) $firstExpected,
            'gte' => (float) $actual >= (float) $firstExpected,
            'lt' => (float) $actual < (float) $firstExpected,
            'lte' => (float) $actual <= (float) $firstExpected,
            'between' => count($expected) >= 2
                && (float) $actual >= (float) $expected[0]
                && (float) $actual <= (float) $expected[1],
            default => false,
        };
    }

    private function fieldValue(Lead $lead, string $field): mixed
    {
        return match ($field) {
            'industry' => data_get($lead->custom_fields ?: [], 'industry'),
            'behavior.activity_count' => $lead->activities()->count(),
            'behavior.has_follow_up' => filled($lead->next_activity_at),
            'behavior.days_since_last_activity' => $lead->last_activity_at
                ? $lead->last_activity_at->diffInDays(now())
                : null,
            'completeness.percent' => $this->completenessPercent($lead),
            default => Str::startsWith($field, 'custom_fields.')
                ? data_get($lead->custom_fields ?: [], Str::after($field, 'custom_fields.'))
                : data_get($lead->getAttributes(), $field),
        };
    }

    private function expectedValues(mixed $value): array
    {
        if (is_string($value)) {
            $decoded = json_decode($value, true);

            if (json_last_error() === JSON_ERROR_NONE) {
                return Arr::wrap($decoded);
            }

            return str_contains($value, ',')
                ? array_map(fn (string $item): string => trim($item), explode(',', $value))
                : [$value];
        }

        return Arr::wrap($value);
    }

    private function completenessPercent(Lead $lead): int
    {
        $fields = ['company_name', 'contact_name', 'phone', 'email', 'source', 'area_id'];
        $filled = collect($fields)->filter(fn (string $field): bool => filled($lead->{$field}))->count();

        return (int) round($filled / count($fields) * 100);
    }

    private function completenessItems(Lead $lead): array
    {
        return array_values(array_filter([
            $lead->phone ? ['name' => '手机号完整', 'score' => 5] : null,
            $lead->email ? ['name' => '邮箱完整', 'score' => 5] : null,
            $lead->company_name ? ['name' => '公司名称完整', 'score' => 10] : null,
            $lead->contact_name ? ['name' => '联系人完整', 'score' => 5] : null,
            $lead->source ? ['name' => '来源完整', 'score' => 3] : null,
            $lead->area_id ? ['name' => '地区完整', 'score' => 3] : null,
        ]));
    }

    private function behaviorItems(Lead $lead): array
    {
        return array_values(array_filter([
            $lead->last_activity_at ? ['name' => '已有跟进记录', 'score' => 5] : null,
            $lead->next_activity_at ? ['name' => '已有下次跟进', 'score' => 5] : null,
        ]));
    }
}

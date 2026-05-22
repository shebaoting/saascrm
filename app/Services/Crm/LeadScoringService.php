<?php

namespace App\Services\Crm;

use App\Models\Lead;
use App\Models\LeadScoreRule;
use Illuminate\Support\Arr;

class LeadScoringService
{
    public function score(Lead $lead): int
    {
        $score = 0;

        LeadScoreRule::query()
            ->where('tenant_id', $lead->tenant_id)
            ->where('is_active', true)
            ->get()
            ->each(function (LeadScoreRule $rule) use ($lead, &$score): void {
                if ($this->matches($lead, $rule)) {
                    $score += (int) $rule->score;
                }
            });

        if ($lead->phone) {
            $score += 5;
        }

        if ($lead->email) {
            $score += 5;
        }

        if ($lead->company_name) {
            $score += 10;
        }

        return max(0, $score);
    }

    public function refresh(Lead $lead): Lead
    {
        $lead->forceFill(['score' => $this->score($lead)])->save();

        return $lead->refresh();
    }

    private function matches(Lead $lead, LeadScoreRule $rule): bool
    {
        $actual = data_get($lead->getAttributes(), $rule->field);
        $expected = $rule->value;
        $firstExpected = Arr::first(Arr::wrap($expected));

        return match ($rule->operator) {
            'eq' => (string) $actual === (string) $firstExpected,
            'contains' => str_contains((string) $actual, (string) $firstExpected),
            'in' => in_array((string) $actual, array_map('strval', Arr::wrap($expected)), true),
            'not_empty' => filled($actual),
            'gt' => (float) $actual > (float) $firstExpected,
            'lt' => (float) $actual < (float) $firstExpected,
            default => false,
        };
    }
}

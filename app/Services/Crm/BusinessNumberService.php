<?php

namespace App\Services\Crm;

use App\Models\BusinessNumberRule;
use Illuminate\Support\Facades\DB;

class BusinessNumberService
{
    /**
     * @return array<string, string>
     */
    public static function moduleLabels(): array
    {
        return [
            'lead' => '线索编号',
            'customer' => '客户编号',
            'quote' => '报价号',
            'order' => '订单号',
        ];
    }

    public function next(int $tenantId, string $module): string
    {
        return DB::transaction(function () use ($tenantId, $module): string {
            $rule = BusinessNumberRule::query()
                ->where('tenant_id', $tenantId)
                ->where('module', $module)
                ->lockForUpdate()
                ->first();

            if (! $rule) {
                $rule = BusinessNumberRule::create($this->defaultRule($tenantId, $module));
                $rule->refresh();
            }

            if (! $rule->is_active) {
                return $this->fallback($module);
            }

            $sequenceKey = $this->sequenceKey($rule->reset_period);
            $sequence = $rule->last_sequence_key === $sequenceKey
                ? ((int) $rule->current_sequence) + 1
                : 1;

            $rule->forceFill([
                'current_sequence' => $sequence,
                'last_sequence_key' => $sequenceKey,
            ])->save();

            return $this->render($rule, $sequence);
        });
    }

    /**
     * @return array<string, mixed>
     */
    private function defaultRule(int $tenantId, string $module): array
    {
        $prefix = match ($module) {
            'lead' => 'LD',
            'customer' => 'CU',
            'quote' => 'QT',
            'order' => 'SO',
            default => strtoupper(substr($module, 0, 2)),
        };

        return [
            'tenant_id' => $tenantId,
            'module' => $module,
            'name' => self::moduleLabels()[$module] ?? $module,
            'prefix' => $prefix,
            'pattern' => '{PREFIX}{YYYY}{MM}{DD}{SEQ}',
            'sequence_length' => 4,
            'reset_period' => 'daily',
            'current_sequence' => 0,
            'is_active' => true,
        ];
    }

    private function sequenceKey(string $period): string
    {
        return match ($period) {
            'yearly' => now()->format('Y'),
            'monthly' => now()->format('Ym'),
            'never' => 'all',
            default => now()->format('Ymd'),
        };
    }

    private function render(BusinessNumberRule $rule, int $sequence): string
    {
        $seq = str_pad((string) $sequence, max(1, (int) $rule->sequence_length), '0', STR_PAD_LEFT);

        $tokens = [
            '{PREFIX}' => (string) $rule->prefix,
            '{SUFFIX}' => (string) $rule->suffix,
            '{YYYY}' => now()->format('Y'),
            '{YY}' => now()->format('y'),
            '{MM}' => now()->format('m'),
            '{DD}' => now()->format('d'),
            '{SEQ}' => $seq,
        ];

        return strtr($rule->pattern ?: '{PREFIX}{YYYY}{MM}{DD}{SEQ}', $tokens);
    }

    private function fallback(string $module): string
    {
        return strtoupper(substr($module, 0, 2)).now()->format('YmdHis');
    }
}

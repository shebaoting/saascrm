<?php

namespace App\Services\Crm;

use App\Models\Quote;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class QuoteVersionService
{
    public function createNewVersion(Quote $quote): Quote
    {
        return DB::transaction(function () use ($quote): Quote {
            $quote->refresh()->load('items');

            $rootQuoteId = $quote->source_quote_id ?: $quote->id;
            $nextVersion = $this->nextVersion($quote);

            $newQuote = $quote->replicate([
                'quote_number',
                'version',
                'status',
                'accepted_at',
                'superseded_at',
                'pdf_path',
                'created_at',
                'updated_at',
                'deleted_at',
            ]);

            $newQuote->forceFill([
                'quote_number' => $this->nextQuoteNumber($quote, $nextVersion),
                'version' => $nextVersion,
                'source_quote_id' => $rootQuoteId,
                'status' => 'draft',
                'accepted_at' => null,
                'superseded_at' => null,
                'pdf_path' => null,
                'user_id' => Auth::id() ?: $quote->user_id,
            ])->save();

            foreach ($quote->items as $item) {
                $newItem = $item->replicate(['quote_id', 'created_at', 'updated_at']);
                $newItem->forceFill([
                    'tenant_id' => $quote->tenant_id,
                    'quote_id' => $newQuote->id,
                ])->save();
            }

            if (! in_array($quote->status, ['accepted', 'expired'], true)) {
                $quote->forceFill([
                    'status' => 'expired',
                    'superseded_at' => now(),
                ])->save();
            }

            app(QuoteCalculatorService::class)->recalculate($newQuote);

            return $newQuote->refresh();
        });
    }

    private function nextVersion(Quote $quote): int
    {
        $query = Quote::query()
            ->where('tenant_id', $quote->tenant_id);

        if ($quote->opportunity_id) {
            $query->where('opportunity_id', $quote->opportunity_id);
        } else {
            $rootQuoteId = $quote->source_quote_id ?: $quote->id;
            $query->where(function ($query) use ($rootQuoteId): void {
                $query->whereKey($rootQuoteId)
                    ->orWhere('source_quote_id', $rootQuoteId);
            });
        }

        return ((int) $query->max('version')) + 1;
    }

    private function nextQuoteNumber(Quote $quote, int $version): string
    {
        $base = preg_replace('/-V\d+$/', '', $quote->sourceQuote?->quote_number ?: $quote->quote_number);
        $candidate = $base.'-V'.$version;
        $suffix = 1;

        while (Quote::query()->where('tenant_id', $quote->tenant_id)->where('quote_number', $candidate)->exists()) {
            $suffix++;
            $candidate = $base.'-V'.$version.'-'.$suffix;
        }

        return $candidate;
    }
}

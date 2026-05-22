<?php

namespace App\Services\Crm;

use App\Models\Quote;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Support\Facades\Storage;

class QuotePdfService
{
    public function generate(Quote $quote): Quote
    {
        app(QuoteCalculatorService::class)->recalculate($quote);

        $quote->refresh()->load(['tenant', 'customer', 'contact', 'items']);
        app(PlanLimitService::class)->assertFeature($quote->tenant, 'pdf');

        $options = new Options;
        $options->set('defaultFont', 'DejaVu Sans');
        $options->set('isRemoteEnabled', true);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml(view('quotes.pdf', [
            'quote' => $quote,
            'logoDataUri' => $this->logoDataUri($quote),
            'terms' => data_get($quote->tenant?->settings, 'quote_terms'),
        ])->render(), 'UTF-8');
        $dompdf->setPaper('A4');
        $dompdf->render();

        $path = sprintf(
            'tenants/%s/quotes/%s/%s-v%s.pdf',
            $quote->tenant_id,
            now()->format('Ymd'),
            $quote->quote_number,
            $quote->version,
        );

        Storage::disk('local')->put($path, $dompdf->output());

        $quote->forceFill(['pdf_path' => $path])->save();

        return $quote->refresh();
    }

    private function logoDataUri(Quote $quote): ?string
    {
        $path = $quote->tenant?->logo_path;

        if (! $path || ! Storage::disk('local')->exists($path)) {
            return null;
        }

        $mime = Storage::disk('local')->mimeType($path) ?: 'image/png';
        $content = Storage::disk('local')->get($path);

        return 'data:'.$mime.';base64,'.base64_encode($content);
    }
}

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

        $options = new Options;
        $options->set('defaultFont', 'DejaVu Sans');
        $options->set('isRemoteEnabled', true);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml(view('quotes.pdf', ['quote' => $quote])->render(), 'UTF-8');
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
}

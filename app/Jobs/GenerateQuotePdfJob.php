<?php

namespace App\Jobs;

use App\Models\Quote;
use App\Services\Crm\NotificationService;
use App\Services\Crm\QuotePdfService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class GenerateQuotePdfJob implements ShouldQueue
{
    use Queueable;

    public function __construct(public int $quoteId)
    {
        $this->onQueue('pdf');
    }

    public function handle(QuotePdfService $service, NotificationService $notifications): void
    {
        $quote = Quote::query()->findOrFail($this->quoteId);

        $quote = $service->generate($quote);

        if ($quote->user_id) {
            $notifications->send(
                $quote->tenant_id,
                $quote->user_id,
                'quote_pdf_generated',
                '报价 PDF 已生成',
                $quote->quote_number.' 的 PDF 已生成。',
                $quote,
            );
        }
    }
}

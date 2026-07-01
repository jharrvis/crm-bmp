<?php

namespace App\Services;

use App\Models\Invoice;
use Barryvdh\DomPDF\Facade\Pdf;

class InvoicePdfService
{
    /**
     * Generate PDF from an invoice model.
     */
    public function generate(Invoice $invoice)
    {
        $invoice->loadMissing(['client.branch', 'items.subscription.package']);

        $pdf = Pdf::loadView('invoices.pdf', [
            'invoice' => $invoice,
            'isPdf' => true,
        ]);

        $pdf->setPaper('a4', 'portrait');

        return $pdf;
    }

    /**
     * Download the invoice PDF.
     */
    public function download(Invoice $invoice)
    {
        $pdf = $this->generate($invoice);
        $filename = 'Invoice-'.str_replace('/', '-', $invoice->invoice_number).'.pdf';

        return $pdf->download($filename);
    }

    /**
     * Stream the invoice PDF in the browser.
     */
    public function stream(Invoice $invoice)
    {
        $pdf = $this->generate($invoice);
        $filename = 'Invoice-'.str_replace('/', '-', $invoice->invoice_number).'.pdf';

        return $pdf->stream($filename);
    }
}

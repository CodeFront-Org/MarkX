<?php

namespace App\Services;

use App\Models\Quote;
use App\Models\Invoice;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Exception;
use Illuminate\Support\Facades\Log;

class PdfService
{
    protected $maxRetries = 3;
    protected $retryDelay = 1; // seconds

    /**
     * Generate a PDF for a quote
     *
     * @param Quote $quote
     * @return \Barryvdh\DomPDF\PDF
     */
    public function generateQuotePdf(Quote $quote)
    {
        return Pdf::loadView('pdf.quote', compact('quote'));
    }

    /**
     * Generate a PDF for an invoice
     *
     * @param Invoice $invoice
     * @return \Barryvdh\DomPDF\PDF
     * @throws Exception
     */
    public function generateInvoicePdf(Invoice $invoice)
    {
        try {
            $pdf = PDF::loadView('pdf.invoice', compact('invoice'));

            // Validate PDF was generated correctly
            if (empty($pdf->output())) {
                throw new Exception('Generated PDF file is invalid or empty');
            }

            return $pdf;
        } catch (Exception $e) {
            Log::error('PDF generation failed', [
                'invoice_id' => $invoice->id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Save a quote PDF to storage
     *
     * @param Quote $quote
     * @return string The path where the PDF was saved
     */
    public function saveQuotePdf(Quote $quote)
    {
        $pdf = $this->generateQuotePdf($quote);
        $path = storage_path('app/public/quotes/' . $quote->id . '.pdf');
        
        // Ensure the directory exists
        if (!file_exists(dirname($path))) {
            mkdir(dirname($path), 0777, true);
        }
        
        $pdf->save($path);
        return $path;
    }

    /**
     * Save an invoice PDF to storage
     *
     * @param Invoice $invoice
     * @return string The path where the PDF was saved
     */
    public function saveInvoicePdf(Invoice $invoice)
    {
        $pdf = $this->generateInvoicePdf($invoice);
        $path = storage_path('app/public/invoices/' . $invoice->invoice_number . '.pdf');
        
        // Ensure the directory exists
        if (!file_exists(dirname($path))) {
            mkdir(dirname($path), 0777, true);
        }
        
        $pdf->save($path);
        return $path;
    }

    /**
     * Stream a quote PDF to the browser
     *
     * @param Quote $quote
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function streamQuotePdf(Quote $quote)
    {
        return $this->generateQuotePdf($quote)->stream("quote-{$quote->id}.pdf");
    }

    /**
     * Stream an invoice PDF to the browser
     *
     * @param Invoice $invoice
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws Exception
     */
    public function streamInvoicePdf(Invoice $invoice)
    {
        return $this->withRetry(function () use ($invoice) {
            $pdf = $this->generateInvoicePdf($invoice);
            return $pdf->stream("invoice_{$invoice->invoice_number}.pdf");
        });
    }

    /**
     * Retry logic for executing a callback
     *
     * @param callable $callback
     * @return mixed
     * @throws Exception
     */
    protected function withRetry(callable $callback)
    {
        $attempt = 1;
        $lastException = null;

        while ($attempt <= $this->maxRetries) {
            try {
                return $callback();
            } catch (Exception $e) {
                $lastException = $e;
                Log::warning("PDF generation attempt {$attempt} failed", [
                    'error' => $e->getMessage()
                ]);

                if ($attempt < $this->maxRetries) {
                    sleep($this->retryDelay * $attempt);
                }
                $attempt++;
            }
        }

        throw $lastException ?? new Exception('PDF generation failed after ' . $this->maxRetries . ' attempts');
    }

    /**
     * Cleanup temporary PDF files older than 1 hour
     */
    public function cleanup()
    {
        $files = Storage::files('temp');
        foreach ($files as $file) {
            if (Storage::lastModified($file) < now()->subHour()->timestamp) {
                Storage::delete($file);
            }
        }
    }
}
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
        $quoteData = $this->prepareQuoteData($quote);
        return Pdf::loadView('pdf.quote', $quoteData);
    }

    /**
     * Prepare quote data with approved items only for PDF generation
     *
     * @param Quote $quote
     * @return array
     */
    protected function prepareQuoteData(Quote $quote)
    {
        $approvedItems = $quote->items->filter(function ($item) {
            return $item->approved;
        });

        $approvedTotal = $approvedItems->sum(function ($item) {
            return $item->quantity * $item->price;
        });

        return [
            'quote' => $quote,
            'items' => $approvedItems,
            'approvedTotal' => $approvedTotal,
            'hasUnapprovedItems' => $quote->items->count() !== $approvedItems->count()
        ];
    }

    /**
     * Generate a PDF for an invoice
     *
     * @param Invoice $invoice
     * @return \Barryvdh\DomPDF\PDF
     * @throws Exception
     */

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
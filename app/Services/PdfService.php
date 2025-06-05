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
     * @param bool $showInternalDetails Whether to show internal details like approval status
     * @return \Barryvdh\DomPDF\PDF
     */
    public function generateQuotePdf(Quote $quote, bool $showInternalDetails = false)
    {
        $quoteData = $this->prepareQuoteData($quote, $showInternalDetails);
        $pdf = Pdf::loadView('pdf.quote', $quoteData);
        
        // Configure PDF to properly handle the letterhead
        $pdf->setOptions([
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => true,
            'dpi' => 150,
            'defaultFont' => 'sans-serif',
            'enable_php' => true,
            'enable_remote' => true,
            'enable_javascript' => true,
        ]);
        
        return $pdf;
    }

    /**
     * Prepare quote data for PDF generation
     * For completed quotes, show only approved items
     * For non-completed quotes, show all items
     *
     * @param Quote $quote
     * @param bool $showInternalDetails Whether to show internal details like approval status
     * @return array
     */
    protected function prepareQuoteData(Quote $quote, bool $showInternalDetails = false)
    {
        // For completed quotes, show only approved items
        // For non-completed quotes, show all items
        $items = $quote->status === 'completed' 
            ? $quote->items->filter(function ($item) {
                return $item->approved;
              }) 
            : $quote->items;

        $itemsTotal = $items->sum(function ($item) {
            return $item->quantity * $item->price;
        });

        $approvedItems = $quote->items->filter(function ($item) {
            return $item->approved;
        });

        $approvedTotal = $approvedItems->sum(function ($item) {
            return $item->quantity * $item->price;
        });

        return [
            'quote' => $quote,
            'items' => $items,
            'itemsTotal' => $itemsTotal,
            'approvedTotal' => $approvedTotal,
            'showOnlyApproved' => $quote->status === 'completed',
            'hasUnapprovedItems' => $quote->items->count() !== $approvedItems->count(),
            'showInternalDetails' => $showInternalDetails
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
     * @param bool $showInternalDetails Whether to show internal details like approval status
     * @return string The path where the PDF was saved
     */
    public function saveQuotePdf(Quote $quote, bool $showInternalDetails = false)
    {
        $pdf = $this->generateQuotePdf($quote, $showInternalDetails);
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
     * @param bool $showInternalDetails Whether to show internal details like approval status
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function streamQuotePdf(Quote $quote, bool $showInternalDetails = false)
    {
        return $this->generateQuotePdf($quote, $showInternalDetails)->stream("quote-{$quote->id}.pdf");
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
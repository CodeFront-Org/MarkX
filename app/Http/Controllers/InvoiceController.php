<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Quote;
use App\Services\PdfService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Exception;
use Illuminate\Support\Facades\Log;

class InvoiceController extends Controller
{
    protected $pdfService;
    protected $maxRetries = 3;

    public function __construct(PdfService $pdfService)
    {
        $this->middleware('auth');
        $this->pdfService = $pdfService;
    }

    public function index(Request $request)
    {
        $query = Auth::user()->role === 'manager' 
            ? Invoice::query()
            : Invoice::where('user_id', Auth::id());

        // Apply search filters
        if ($request->filled('invoice_number')) {
            $query->where('invoice_number', 'like', '%' . $request->invoice_number . '%');
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('min_amount')) {
            $query->where('amount', '>=', $request->min_amount);
        }

        if ($request->filled('max_amount')) {
            $query->where('amount', '<=', $request->max_amount);
        }

        if ($request->filled('due_date')) {
            $query->whereDate('due_date', '<=', $request->due_date);
        }

        // Search by product item name
        if ($request->filled('product_item')) {
            $query->whereHas('quote.items', function($q) use ($request) {
                $q->where('item', 'like', '%' . $request->product_item . '%');
            });
        }

        // Search by minimum quantity
        if ($request->filled('min_quantity')) {
            $query->whereHas('quote.items', function($q) use ($request) {
                $q->where('quantity', '>=', $request->min_quantity);
            });
        }

        // Search by maximum quantity
        if ($request->filled('max_quantity')) {
            $query->whereHas('quote.items', function($q) use ($request) {
                $q->where('quantity', '<=', $request->max_quantity);
            });
        }

        // Search by marketer (user who created the quote)
        if ($request->filled('marketer')) {
            $query->whereHas('quote.user', function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->marketer . '%')
                    ->orWhere('email', 'like', '%' . $request->marketer . '%');
            });
        }

        $invoices = $query->with(['quote.items', 'quote.user', 'user'])
            ->latest()
            ->paginate(10)
            ->withQueryString();

        if ($request->ajax()) {
            return response()->json([
                'html' => view('invoices.partials.invoice-list', compact('invoices'))->render(),
                'pagination' => $invoices->links()->toHtml()
            ]);
        }

        return view('invoices.index', compact('invoices'));
    }

    public function create(Request $request)
    {
        $this->authorize('create', Invoice::class);
        
        $quote = null;
        if ($request->has('quote')) {
            $quote = Quote::findOrFail($request->quote);
            if (!$quote->isConvertible()) {
                return back()->with('error', 'Only successful quotes can have invoices generated.');
            }
        }
        
        return view('invoices.create', compact('quote'));
    }

    public function store(Request $request)
    {
        $this->authorize('create', Invoice::class);

        $quote = Quote::findOrFail($request->quote);
        if (!$quote->isConvertible()) {
            return redirect()->route('quotes.show', $quote)
                ->with('error', 'Only successful quotes can have invoices generated.');
        }

        try {
            $invoice = DB::transaction(function() use ($quote) {
                // Create invoice first
                $invoice = $quote->invoice()->create([
                    'user_id' => $quote->user_id,
                    'amount' => $quote->amount,
                    'status' => 'draft',
                    'due_date' => now()->addDays(30)
                ]);

                // Then mark quote as converted
                $quote->markAsConverted();

                return $invoice;
            }, 3); // Add retry attempts for deadlocks

            return redirect()->route('invoices.show', $invoice)
                ->with('success', 'Invoice generated successfully.');
        } catch (Exception $e) {
            Log::error('Failed to create invoice', [
                'quote_id' => $quote->id,
                'error' => $e->getMessage()
            ]);
            return redirect()->route('quotes.show', $quote)
                ->with('error', 'Failed to generate invoice. Please try again.');
        }
    }

    public function show(Invoice $invoice)
    {
        $this->authorize('view', $invoice);
        return view('invoices.show', compact('invoice'));
    }

    public function edit(Invoice $invoice)
    {
        $this->authorize('update', $invoice);
        
        if ($invoice->status !== 'draft') {
            return back()->with('error', 'Only draft invoices can be edited.');
        }

        return view('invoices.edit', compact('invoice'));
    }

    public function update(Request $request, Invoice $invoice)
    {
        $this->authorize('update', $invoice);

        if ($invoice->status !== 'draft') {
            return back()->with('error', 'Only draft invoices can be updated.');
        }

        $validated = $request->validate([
            'amount' => 'required|numeric|min:0',
            'due_date' => 'required|date|after:today'
        ]);

        $invoice->update($validated);

        return redirect()->route('invoices.show', $invoice)
            ->with('success', 'Invoice updated successfully.');
    }

    public function destroy(Invoice $invoice)
    {
        $this->authorize('delete', $invoice);

        try {
            DB::transaction(function() use ($invoice) {
                $invoice->delete();
            });

            return redirect()->route('invoices.index')
                ->with('success', 'Invoice deleted successfully.');
        } catch (Exception $e) {
            Log::error('Failed to delete invoice', [
                'invoice_id' => $invoice->id,
                'error' => $e->getMessage()
            ]);
            return back()->with('error', 'Failed to delete invoice. Please try again.');
        }
    }

    public function send(Invoice $invoice)
    {
        $this->authorize('send', $invoice);

        try {
            DB::transaction(function() use ($invoice) {
                $invoice->markAsFinal();
                
                // Generate PDF before marking as sent to ensure it can be generated
                $this->pdfService->generateInvoicePdf($invoice);
            });

            // TODO: Send email with invoice PDF
            return redirect()->route('invoices.show', $invoice)
                ->with('success', 'Invoice marked as final successfully.');
        } catch (Exception $e) {
            Log::error('Failed to send invoice', [
                'invoice_id' => $invoice->id,
                'error' => $e->getMessage()
            ]);
            return back()->with('error', 'Failed to finalize invoice. Please try again.');
        }
    }

    public function markAsPaid(Invoice $invoice)
    {
        $this->authorize('markAsPaid', $invoice);

        if (!in_array($invoice->status, ['final', 'overdue'])) {
            return back()->with('error', 'Only final or overdue invoices can be marked as paid.');
        }

        try {
            DB::transaction(function() use ($invoice) {
                $invoice->markAsPaid();
            });

            return redirect()->route('invoices.show', $invoice)
                ->with('success', 'Invoice marked as paid successfully.');
        } catch (Exception $e) {
            Log::error('Failed to mark invoice as paid', [
                'invoice_id' => $invoice->id,
                'error' => $e->getMessage()
            ]);
            return back()->with('error', 'Failed to mark invoice as paid. Please try again.');
        }
    }

    public function downloadPdf(Invoice $invoice)
    {
        $this->authorize('view', $invoice);
        
        try {
            return $this->pdfService->streamInvoicePdf($invoice);
        } catch (Exception $e) {
            Log::error('PDF generation failed', [
                'invoice_id' => $invoice->id,
                'error' => $e->getMessage()
            ]);
            
            return back()->with('error', 'Failed to generate PDF. Please try again later.');
        }
    }

    public function checkOverdue()
    {
        $overdue = Invoice::where('status', 'final')
            ->whereDate('due_date', '<', now())
            ->whereNull('paid_at')
            ->get();

        foreach ($overdue as $invoice) {
            $invoice->markAsOverdue();
        }

        return back()->with('success', 'Overdue invoices updated.');
    }

    public function billing()
    {
        $invoices = Auth::user()->role === 'manager' 
            ? Invoice::with('quote')->latest()->take(5)->get()
            : Invoice::where('user_id', Auth::id())->with('quote')->latest()->take(5)->get();

        $recentTransactions = Invoice::where(function($query) {
                if (Auth::user()->role !== 'manager') {
                    $query->where('user_id', Auth::id());
                }
            })
            ->where('status', '!=', 'draft')
            ->whereDate('created_at', '>=', now()->subDays(30))
            ->latest()
            ->get();

        return view('billing', compact('invoices', 'recentTransactions'));
    }
}

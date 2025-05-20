<?php

namespace App\Http\Controllers;

use App\Models\Quote;
use App\Models\QuoteItem;
use App\Services\PdfService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class QuoteController extends Controller
{
    protected $pdfService;

    public function __construct(PdfService $pdfService)
    {
        $this->middleware('auth');
        $this->pdfService = $pdfService;
    }

    public function index(Request $request)
    {
        $query = Auth::user()->role === 'manager'
            ? Quote::query()
            : Quote::where('user_id', Auth::id());

        // Apply search filters
        if ($request->filled('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
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

        // Date range filter
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Search by product item name
        if ($request->filled('product_item')) {
            $query->whereHas('items', function($q) use ($request) {
                $q->where('item', 'like', '%' . $request->product_item . '%');
            });
        }

        // Search by minimum quantity
        if ($request->filled('min_quantity')) {
            $query->whereHas('items', function($q) use ($request) {
                $q->where('quantity', '>=', $request->min_quantity);
            });
        }

        // Search by maximum quantity
        if ($request->filled('max_quantity')) {
            $query->whereHas('items', function($q) use ($request) {
                $q->where('quantity', '<=', $request->max_quantity);
            });
        }

        // Search by marketer (user who created the quote)
        if ($request->filled('marketer')) {
            $query->whereHas('user', function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->marketer . '%')
                    ->orWhere('email', 'like', '%' . $request->marketer . '%');
            });
        }

        $quotes = $query->with(['user', 'items'])
            ->latest()
            ->paginate(10)
            ->withQueryString();

        if ($request->ajax()) {
            return response()->json([
                'html' => view('quotes.partials.quote-list', compact('quotes'))->render(),
                'pagination' => $quotes->links()->toHtml()
            ]);
        }

        return view('quotes.index', compact('quotes'));
    }

    public function create()
    {
        $latestQuoteId = Quote::max('id') ?? 0;
        $nextQuoteId = $latestQuoteId + 1;
        $reference = 'Q' . str_pad($nextQuoteId, 6, '0', STR_PAD_LEFT);
        
        return view('quotes.create', compact('reference'));
    }

    public function store(Request $request)
    {
        $this->authorize('create', Quote::class);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'valid_until' => 'required|date|after:today',
            'items' => 'required|array|min:1',
            'items.*.item' => 'required|string',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0'
        ]);

        $quote = DB::transaction(function() use ($validated, $request) {
            $totalAmount = collect($request->items)->sum(function($item) {
                return $item['quantity'] * $item['price'];
            });
            
            // Get the latest quote ID
            $latestQuoteId = Quote::max('id') ?? 0;
            $nextQuoteId = $latestQuoteId + 1;
            
            $quote = Quote::create([
                ...$validated,
                'amount' => $totalAmount,
                'status' => 'pending',
                'user_id' => Auth::id(),
                'reference' => 'Q' . str_pad($nextQuoteId, 6, '0', STR_PAD_LEFT)
            ]);

            foreach ($request->items as $item) {
                $quote->items()->create([
                    'item' => $item['item'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'approved' => false,
                    'comment' => $item['comment'] ?? null
                ]);
            }

            return $quote;
        });

        return redirect()->route('quotes.index')
            ->with('success', 'Quote created successfully.');
    }

    public function show(Quote $quote)
    {
        $this->authorize('view', $quote);
        return view('quotes.show', compact('quote'));
    }

    public function edit(Quote $quote)
    {
        $this->authorize('update', $quote);
        return view('quotes.edit', compact('quote'));
    }

    public function update(Request $request, Quote $quote)
    {
        $this->authorize('update', $quote);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'valid_until' => 'required|date|after:today',
            'items' => 'required|array|min:1',
            'items.*.item' => 'required|string',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0',
            'items.*.approved' => 'boolean',
            'items.*.reason' => [
                'required_if:items.*.approved,false',
                'string',
                'in:out_of_stock,discontinued,price_unavailable,lead_time_too_long,other'
            ],
            'items.*.reason_details' => [
                'required_if:items.*.reason,other',
                'nullable',
                'string'
            ],
            'unquoted_items' => 'nullable|array',
            'unquoted_items.*.item' => 'required|string',
            'unquoted_items.*.quantity' => 'required|integer|min:1',
            'unquoted_items.*.reason' => [
                'required',
                'string',
                'in:out_of_stock,discontinued,price_unavailable,lead_time_too_long,other'
            ],
            'unquoted_items.*.reason_details' => [
                'required_if:unquoted_items.*.reason,other',
                'nullable',
                'string'
            ]
        ]);

        DB::transaction(function() use ($validated, $request, $quote) {
            $totalAmount = collect($request->items)->sum(function($item) {
                return $item['quantity'] * $item['price'];
            });
            
            $quote->update([
                ...$validated,
                'amount' => $totalAmount
            ]);

            // Delete existing items and create new ones
            $quote->items()->delete();
            $quote->unquotedItems()->delete();

            // Collect items that weren't approved
            $nonApprovedItems = collect($request->items)
                ->reject(function($item) {
                    return $item['approved'] ?? false;
                })
                ->map(function($item) {
                    // Create an unquoted item for each non-approved item
                    return [
                        'item' => $item['item'],
                        'quantity' => $item['quantity'],
                        'reason' => $item['reason'] ?? 'price_unavailable',  // Default reason
                        'reason_details' => $item['reason_details'] ?? null
                    ];
                });

            // Create the quote items
            foreach ($request->items as $item) {
                $quote->items()->create([
                    'item' => $item['item'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'approved' => $item['approved'] ?? false,
                    'comment' => $item['comment'] ?? null
                ]);
            }

            // Create unquoted items for non-approved items
            foreach ($nonApprovedItems as $item) {
                $quote->unquotedItems()->create($item);
            }
        });

        return redirect()->route('quotes.show', $quote)
            ->with('success', 'Quote updated successfully.');
    }

    public function destroy(Quote $quote)
    {
        $this->authorize('delete', $quote);
        
        $quote->delete();

        return redirect()->route('quotes.index')
            ->with('success', 'Quote deleted successfully.');
    }

    public function approve(Quote $quote)
    {
        $this->authorize('approve', $quote);

        DB::transaction(function() use ($quote) {
            $quote->update(['status' => 'approved']);
        });

        return redirect()->route('quotes.show', $quote)
            ->with('success', 'Quote approved successfully.');
    }

    public function reject(Request $request, Quote $quote)
    {
        $this->authorize('reject', $quote);

        $validated = $request->validate([
            'rejection_reason' => 'required|string|in:suspended,credit_limit,pending_payment,policy_violation,other',
            'rejection_details' => 'required_if:rejection_reason,other|nullable|string|max:1000'
        ]);

        DB::transaction(function() use ($quote, $validated) {
            $quote->update([
                'status' => 'rejected',
                'rejection_reason' => $validated['rejection_reason'],
                'rejection_details' => $validated['rejection_details'] ?? null
            ]);

            // If rejection is administrative, reject all items
            if (in_array($validated['rejection_reason'], ['suspended', 'credit_limit', 'pending_payment', 'policy_violation'])) {
                foreach ($quote->items as $item) {
                    $item->update([
                        'approved' => false,
                        'reason' => $validated['rejection_reason'],
                        'reason_details' => $validated['rejection_details'] ?? null
                    ]);
                }
            }
        });

        return redirect()->route('quotes.show', $quote)
            ->with('success', 'Quote rejected successfully.');
    }

    public function convertToInvoice(Quote $quote)
    {
        $this->authorize('convert', $quote);

        if (!$quote->isConvertible()) {
            return back()->with('error', 'Quote cannot be converted to invoice.');
        }

        DB::transaction(function() use ($quote) {
            // Calculate total amount from only approved items
            $approvedAmount = DB::table('quote_items')
                ->where('quote_id', $quote->id)
                ->where('approved', true)
                ->select(DB::raw('SUM(quantity * price) as total'))
                ->value('total') ?? 0;

            if ($approvedAmount <= 0) {
                throw new \Exception('No approved items found in the quote.');
            }

            // Approve the entire quote when all items are approved
            $quote->update(['status' => 'approved']);
        });

        return redirect()->route('invoices.index')
            ->with('success', 'Quote converted to invoice successfully with approved items only.');
    }

    public function downloadPdf(Quote $quote)
    {
        $this->authorize('view', $quote);
        return $this->pdfService->streamQuotePdf($quote);
    }

    public function fetchProductItems(Request $request)
    {
        $search = $request->get('q'); // Select2 uses 'q' parameter
        $page = $request->get('page', 1);
        $pageSize = 10;

        $baseQuery = QuoteItem::select([
                'item',
                DB::raw('COUNT(*) as usage_count'),
                DB::raw('AVG(price) as avg_price'),
                DB::raw('MIN(price) as min_price'),
                DB::raw('MAX(price) as max_price'),
                DB::raw('MAX(created_at) as last_used')
            ])
            ->groupBy('item');

        if ($search) {
            $terms = array_filter(explode(' ', trim($search)));
            if (!empty($terms)) {
                $baseQuery->where(function($q) use ($terms) {
                    foreach ($terms as $term) {
                        $q->where('item', 'like', '%' . $term . '%');
                    }
                });
            }
        }

        $total = $baseQuery->get()->count();
        
        $items = $baseQuery
            ->orderByRaw('COUNT(*) DESC, MAX(created_at) DESC')
            ->offset(($page - 1) * $pageSize)
            ->limit($pageSize)
            ->get()
            ->map(function($item) {
                $usedTimes = $item->usage_count;
                $timePhrase = $usedTimes === 1 ? 'Quoted once' : "Quoted {$usedTimes} times";
                
                return [
                    'id' => $item->item,
                    'text' => $item->item,
                    'price' => round($item->avg_price, 2),
                    'description' => $timePhrase,
                    'lastUsed' => $item->last_used
                ];
            });
        
        return response()->json([
            'results' => $items,
            'pagination' => [
                'more' => ($page * $pageSize) < $total
            ]
        ]);
    }
}

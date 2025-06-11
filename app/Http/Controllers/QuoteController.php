<?php

namespace App\Http\Controllers;

use App\Models\Quote;
use App\Models\QuoteFile;
use App\Models\QuoteItem;
use App\Models\UnquotedItem;
use App\Services\PdfService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class QuoteController extends Controller
{
    protected $pdfService;

    public function __construct(PdfService $pdfService)
    {
        $this->middleware('auth');
        $this->pdfService = $pdfService;
        
        // Prevent lpo_admin users from creating quotes
        $this->middleware('role:rfq_processor')->only(['create', 'store']);
    }

    public function index(Request $request)
    {
        $query = Auth::user()->role === 'rfq_approver' || Auth::user()->role === 'lpo_admin'
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
            'contact_person' => 'nullable|string|max:255',
            'items' => 'required|array|min:1',
            'items.*.item' => 'required|string',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0',
            'items.*.comment' => 'nullable|string',
            'total_rfq_items' => 'required|integer|min:0',
            'files' => 'required|array|min:1',
            'files.*' => 'required|file|max:10240',
            'descriptions.*' => 'nullable|string|max:255',
        ]);

        $quote = DB::transaction(function() use ($validated, $request) {
            $totalAmount = collect($request->items)->sum(function($item) {
                return $item['quantity'] * $item['price'];
            });
            
            $latestQuoteId = Quote::max('id') ?? 0;
            $nextQuoteId = $latestQuoteId + 1;
            
            // Get the current authenticated user
            $currentUser = Auth::user();
            
            $quote = Quote::create([
                ...$validated,
                'amount' => $totalAmount,
                'status' => 'pending_manager',  // New initial status
                'user_id' => Auth::id(),
                'marketer_id' => $currentUser->id, // Set the current user (marketer) as marketer_id
                'reference' => 'Q' . str_pad($nextQuoteId, 6, '0', STR_PAD_LEFT),
                'has_rfq' => true,
                'rfq_files_count' => count($request->file('files'))
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

            if ($request->hasFile('files')) {
                foreach ($request->file('files') as $index => $file) {
                    $originalName = $file->getClientOriginalName();
                    $fileName = Str::random(40) . '.' . $file->getClientOriginalExtension();
                    $path = $file->storeAs("quote-files/{$quote->id}", $fileName, 'public');
                    
                    $quote->files()->create([
                        'original_name' => $originalName,
                        'file_name' => $fileName,
                        'file_type' => $file->getClientMimeType(),
                        'path' => $path,
                        'description' => $request->descriptions[$index] ?? null
                    ]);
                }
            }

            return $quote;
        });

        return redirect()->route('quotes.index')
            ->with('success', 'Quote created successfully and sent for RFQ Approver.');
    }

    public function show(Quote $quote)
    {
        $this->authorize('view', $quote);
        return view('quotes.show', compact('quote'));
    }

    public function edit(Quote $quote)
    {
        $this->authorize('update', $quote);
        
        // Extra check to ensure only lpo_admin can access edit and quote is not completed
        if (!auth()->user()->isLpoAdmin()) {
            return redirect()->route('quotes.index')
                ->with('error', 'Only LPO Admin users can edit quotes.');
        }
        
        if ($quote->status === 'completed') {
            return redirect()->route('quotes.show', $quote)
                ->with('error', 'Completed quotes cannot be edited.');
        }
        
        return view('quotes.edit', compact('quote'));
    }

    public function update(Request $request, Quote $quote)
    {
        $this->authorize('update', $quote);

        // Count total items being processed
        $totalProcessedItems = count($request->items ?? []) + count($request->unquoted_items ?? []);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'valid_until' => 'required|date|after:today',
            'contact_person' => 'nullable|string|max:255',
            'items' => 'required|array|min:1',
            'items.*.item' => 'required|string',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0',
            'items.*.approved' => 'required|in:0,1',
            'items.*.reason' => [
                'required_if:items.*.approved,0',
                'nullable',
                'string',
                'max:1000'
            ],
            'items.*.comment' => 'nullable|string',
            'total_rfq_items' => [
                'required',
                'integer',
                'min:' . $totalProcessedItems,
            ],
            'files' => 'sometimes|array|min:1',
            'files.*' => 'sometimes|file|max:10240',
            'descriptions.*' => 'nullable|string|max:255',
            'amount' => 'sometimes|required|numeric|min:0',
        ]);

        DB::transaction(function() use ($validated, $request, $quote) {
            $totalAmount = collect($request->items)->sum(function($item) {
                return $item['quantity'] * $item['price'];
            });
            
            $quote->update([
                ...$validated,
                'amount' => $totalAmount,
                'has_rfq' => true
            ]);

            // Delete existing items
            $quote->items()->delete();

            // Create the quote items
            foreach ($request->items as $item) {
                $isApproved = isset($item['approved']) && $item['approved'] == '1';
                
                $quote->items()->create([
                    'item' => $item['item'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'approved' => $isApproved,
                    'reason' => !$isApproved ? ($item['reason'] ?? null) : null,
                    'comment' => $item['comment'] ?? null
                ]);
            }

            // Handle new file uploads if any
            if ($request->hasFile('files')) {
                foreach ($request->file('files') as $index => $file) {
                    $originalName = $file->getClientOriginalName();
                    $fileName = Str::random(40) . '.' . $file->getClientOriginalExtension();
                    $path = $file->storeAs("quote-files/{$quote->id}", $fileName, 'public');

                    $quote->files()->create([
                        'original_name' => $originalName,
                        'file_name' => $fileName,
                        'file_type' => $file->getClientMimeType(),
                        'path' => $path,
                        'description' => $request->descriptions[$index] ?? null
                    ]);
                }
                
                // Update RFQ file count
                $quote->updateRfqFileCount();
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
            if (auth()->user()->isRfqApprover() && $quote->status === 'pending_manager') {
                // RFQ approver approves the entire quote to move to customer review
                $quote->update([
                    'status' => 'pending_customer',
                    'approved_at' => now(),
                    'approved_by' => auth()->id()
                ]);
                return redirect()->route('quotes.show', $quote)
                    ->with('success', 'Quote approved. RFQ Processor can now download PDF for customer review.');
            }

            if (auth()->user()->isLpoAdmin() && $quote->status === 'pending_finance') {
                // LPO Admin closes the quote after reviewing and approving items
                $quote->update([
                    'status' => 'completed',
                    'closed_at' => now(),
                    'closed_by' => auth()->id()
                ]);
                return redirect()->route('quotes.show', $quote)
                    ->with('success', 'Quote closed successfully.');
            }

            throw new \Exception('Invalid approval action for current quote status.');
        });
        
        return redirect()->route('quotes.show', $quote)
            ->with('success', 'Quote status updated successfully.');
    }

    public function submitToFinance(Quote $quote)
    {
        $this->authorize('submit-to-finance', $quote);

        if ($quote->status !== 'pending_customer') {
            return back()->with('error', 'Quote must be approved by RFQ approver and reviewed by customer before submitting to LPO Admin.');
        }

        $quote->update(['status' => 'pending_finance']);

        return redirect()->route('quotes.show', $quote)
            ->with('success', 'Quote submitted to LPO Admin for final review.');
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

    public function download(Quote $quote)
    {
        $this->authorize('view', $quote);
        
        // Only show internal details (approval status, etc.) for LPO Admin users
        // For marketers and managers, hide these details as the PDF might be shared with clients
        $showInternalDetails = auth()->user()->isLpoAdmin();
        
        return $this->pdfService->streamQuotePdf($quote, $showInternalDetails);
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

    public function fetchCustomers(Request $request)
    {
        $search = $request->get('q'); // Select2 uses 'q' parameter
        $page = $request->get('page', 1);
        $pageSize = 10;

        $baseQuery = Quote::select([
                'title',
                'description',
                'contact_person',
                DB::raw('COUNT(*) as quote_count'),
                DB::raw('MAX(created_at) as last_quoted')
            ])
            ->groupBy('title', 'description', 'contact_person');

        if ($search) {
            $terms = array_filter(explode(' ', trim($search)));
            if (!empty($terms)) {
                $baseQuery->where(function($q) use ($terms) {
                    foreach ($terms as $term) {
                        $q->where('title', 'like', '%' . $term . '%')
                          ->orWhere('contact_person', 'like', '%' . $term . '%');
                    }
                });
            }
        }

        $total = $baseQuery->get()->count();
        
        $customers = $baseQuery
            ->orderByRaw('COUNT(*) DESC, MAX(created_at) DESC')
            ->offset(($page - 1) * $pageSize)
            ->limit($pageSize)
            ->get()
            ->map(function($customer) {
                $quotedTimes = $customer->quote_count;
                $timePhrase = $quotedTimes === 1 ? 'Quoted once' : "Quoted {$quotedTimes} times";
                
                return [
                    'id' => $customer->title,
                    'text' => $customer->title,
                    'description' => $customer->description,
                    'contact_person' => $customer->contact_person,
                    'quoteInfo' => $timePhrase,
                    'lastQuoted' => $customer->last_quoted
                ];
            });
        
        return response()->json([
            'results' => $customers,
            'pagination' => [
                'more' => ($page * $pageSize) < $total
            ]
        ]);
    }

    public function attachFile(Request $request, Quote $quote)
    {
        $this->authorize('update', $quote);

        $request->validate([
            'file' => 'required|file|max:10240', // 10MB max
            'description' => 'nullable|string|max:255',
        ]);

        $file = $request->file('file');
        $originalName = $file->getClientOriginalName();
        $fileName = Str::random(40) . '.' . $file->getClientOriginalExtension();
        
        // Store file in the quote files folder
        $path = $file->storeAs("quote-files/{$quote->id}", $fileName, 'public');
        
        try {
            $quoteFile = $quote->files()->create([
                'original_name' => $originalName,
                'file_name' => $fileName,
                'file_type' => $file->getClientMimeType(),
                'path' => $path,
                'description' => $request->description
            ]);

            if (!$quoteFile) {
                Storage::disk('public')->delete($path);
                return back()->with('error', 'Failed to attach file to quote. Please try again.');
            }

            // Update the quote's RFQ file count
            $quote->updateRfqFileCount();
            
            return back()->with('success', 'File attached successfully.');
        } catch (\Exception $e) {
            Storage::disk('public')->delete($path);
            Log::error('QuoteFile creation failed: ' . $e->getMessage());
            return back()->with('error', 'An error occurred while attaching the file. Please try again.');
        }
    }

    public function downloadFile(Quote $quote, QuoteFile $file)
    {
        $this->authorize('view', $quote);
        
        $path = Storage::disk('public')->path($file->path);
        return response()->download($path, $file->original_name);
    }

    public function viewFile(Quote $quote, QuoteFile $file)
    {
        $this->authorize('view', $quote);
        
        $path = Storage::disk('public')->path($file->path);
        $contentType = $file->file_type;
        
        // For PDFs and images, display in browser
        if (in_array($contentType, ['application/pdf', 'image/jpeg', 'image/png', 'image/gif'])) {
            return response()->file($path, ['Content-Type' => $contentType]);
        }
        
        // For other file types that can be displayed in browser
        if (in_array($contentType, [
            'text/plain',
            'text/html',
            'text/css',
            'text/javascript',
            'application/json',
            'application/xml',
            'text/xml'
        ])) {
            $content = file_get_contents($path);
            return response($content)->header('Content-Type', $contentType);
        }
        
        // If file type is not supported for browser viewing, fall back to download
        return response()->download($path, $file->original_name);
    }

    public function deleteFile(Quote $quote, QuoteFile $file)
    {
        $this->authorize('update', $quote);
        
        if ($file->quote_id !== $quote->id) {
            abort(404);
        }

        // Check if this is the last file
        if (!$quote->canDeleteFile()) {
            return back()->with('error', 'Cannot delete the last RFQ file. At least one file must remain.');
        }
        
        if (Storage::disk('public')->exists($file->path)) {
            Storage::disk('public')->delete($file->path);
        }
        
        $file->delete();
        
        // Update the quote's RFQ file count
        $quote->updateRfqFileCount();
        
        return back()->with('success', 'File deleted successfully.');
    }

    public function toggleItemApproval(Request $request, $itemId)
    {
        $item = QuoteItem::findOrFail($itemId);
        $quote = $item->quote;
        
        $this->authorize('update', $quote);
        
        if ($quote->status !== 'pending_finance' || !auth()->user()->isLpoAdmin()) {
            return response()->json(['error' => 'Only LPO Admin users can approve items'], 403);
        }
        
        $item->update([
            'approved' => !$item->approved,
            'reason' => !$item->approved ? null : ($item->reason ?? 'Not approved by LPO Admin')
        ]);
        
        return response()->json(['success' => true, 'approved' => $item->approved]);
    }
}

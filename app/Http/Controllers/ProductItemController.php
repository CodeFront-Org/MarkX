<?php

namespace App\Http\Controllers;

use App\Models\Quote;
use App\Models\QuoteItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ProductItemController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $query = QuoteItem::query()
            ->select([
                'quote_items.item',
                DB::raw('COUNT(DISTINCT quote_items.quote_id) as quote_count'),
                DB::raw('SUM(quote_items.quantity) as total_quantity'),
                DB::raw('AVG(quote_items.price) as avg_price'),
                DB::raw('SUM(quote_items.quantity * quote_items.price) as total_value'),
                DB::raw('SUM(CASE WHEN quote_items.approved = 1 THEN 1 ELSE 0 END) * 100.0 / COUNT(*) as success_rate'),
                DB::raw('GROUP_CONCAT(DISTINCT users.name) as marketers'),
                DB::raw('MIN(CASE WHEN quote_items.approved = 0 THEN 1 ELSE 0 END) as has_pending'),
                DB::raw('MIN(quote_items.comment) as latest_comment')
            ])
            ->leftJoin('quotes', 'quotes.id', '=', 'quote_items.quote_id')
            ->leftJoin('users', 'users.id', '=', 'quotes.marketer_id')
            ->groupBy('quote_items.item');

        if (Auth::user()->role !== 'rfq_approver' && Auth::user()->role !== 'lpo_admin') {
            $query->where('quotes.marketer_id', Auth::id());
        }

        // Apply search filters
        if ($request->filled('item')) {
            $query->having('item', 'like', '%' . $request->item . '%');
        }

        if ($request->filled('marketer')) {
            $query->having('marketers', 'like', '%' . $request->marketer . '%');
        }

        if ($request->filled('approved')) {
            if ($request->approved === 'true') {
                $query->having('has_pending', '=', 0);
            } else {
                $query->having('has_pending', '=', 1);
            }
        }

        if ($request->filled('quote_title')) {
            $query->where('quotes.title', 'like', '%' . $request->quote_title . '%');
        }

        $items = $query->orderBy('total_value', 'desc')
            ->paginate(10)
            ->withQueryString();

        if ($request->ajax()) {
            return response()->json([
                'html' => view('product-items.partials.item-list', compact('items'))->render(),
                'pagination' => $items->links()->toHtml()
            ]);
        }

        return view('product-items.index', compact('items'));
    }

    public function create()
    {
        $quotes = Quote::when(Auth::user()->role !== 'rfq_approver' && Auth::user()->role !== 'lpo_admin', function($query) {
            return $query->where('user_id', Auth::id());
        })
        ->latest()
        ->get();

        return view('product-items.create', compact('quotes'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'item' => 'required|string|max:255',
            'quantity' => 'required|integer|min:1',
            'price' => 'required|numeric|min:0',
            'quote_id' => 'required|exists:quotes,id',
            'comment' => 'nullable|string'
        ]);

        $item = QuoteItem::create($validated);

        return redirect()->route('product-items.index')
            ->with('success', 'Product item created successfully.');
    }

    public function edit(QuoteItem $item)
    {
        return view('product-items.edit', compact('item'));
    }

    public function update(Request $request, QuoteItem $item)
    {
        $validated = $request->validate([
            'item' => 'required|string|max:255',
            'quantity' => 'required|integer|min:1',
            'price' => 'required|numeric|min:0',
            'comment' => 'nullable|string'
        ]);

        $item->update($validated);

        return redirect()->route('product-items.index')
            ->with('success', 'Product item updated successfully.');
    }

    public function show($itemName, Request $request)
    {
        $isUsingMysql = DB::connection()->getDriverName() === 'mysql';
        $groupConcatFunction = $isUsingMysql ? 'GROUP_CONCAT' : 'GROUP_CONCAT';

        $item = QuoteItem::query()
            ->select([
                'quote_items.item',
                DB::raw('COUNT(DISTINCT quote_items.quote_id) as quote_count'),
                DB::raw('SUM(quote_items.quantity) as total_quantity'),
                DB::raw('AVG(quote_items.price) as avg_price'),
                DB::raw('SUM(quote_items.quantity * quote_items.price) as total_value'),
                DB::raw('SUM(CASE WHEN quote_items.approved = 1 THEN 1 ELSE 0 END) * 100.0 / COUNT(*) as success_rate'),
                DB::raw($groupConcatFunction . '(DISTINCT users.name) as marketers'),
                DB::raw('MIN(CASE WHEN quote_items.approved = 0 THEN 1 ELSE 0 END) as has_pending'),
                DB::raw('MAX(quote_items.created_at) as latest_created_at'),
                DB::raw('MAX(quote_items.updated_at) as latest_updated_at')
            ])
            ->leftJoin('quotes', 'quotes.id', '=', 'quote_items.quote_id')
            ->leftJoin('users', 'users.id', '=', 'quotes.user_id')
            ->where('quote_items.item', $itemName)
            ->groupBy('quote_items.item')
            ->first();

        if (!$item) {
            abort(404, 'Product item not found');
        }

        // Get quote history with approval filter
        $quoteHistoryQuery = DB::table('quotes as q')
            ->select(
                'q.id as quote_id',
                'q.reference',
                'q.title',
                'q.status as quote_status',
                'q.created_at',
                'qi.quantity',
                'qi.price',
                'qi.approved',
                'qi.comment',
                'u.name as marketer_name',
                DB::raw('qi.quantity * qi.price as amount')
            )
            ->join('quote_items as qi', 'q.id', '=', 'qi.quote_id')
            ->leftJoin('users as u', 'q.user_id', '=', 'u.id')
            ->where('qi.item', $itemName);

        // Apply approval status filter
        if ($request->filled('approved_status')) {
            if ($request->approved_status === 'approved') {
                $quoteHistoryQuery->where('qi.approved', 1);
            } elseif ($request->approved_status === 'not_approved') {
                $quoteHistoryQuery->where('qi.approved', 0);
            }
        }

        $quoteHistory = $quoteHistoryQuery->orderBy('q.created_at', 'desc')->get();

        // Calculate approval counts
        $approvedCount = $quoteHistory->where('approved', 1)->count();
        $pendingCount = $quoteHistory->where('approved', 0)->count();

        return view('product-items.show', compact('item', 'quoteHistory', 'approvedCount', 'pendingCount'));
    }

    public function destroy(QuoteItem $item)
    {
        $item->delete();

        return redirect()->route('product-items.index')
            ->with('success', 'Product item deleted successfully.');
    }

    public function reports(Request $request)
    {
        $query = QuoteItem::select([
            'quote_items.*',
            'quotes.title as quote_title',
            'quotes.status as quote_status',
            'users.name as marketer_name'
        ])
        ->join('quotes', 'quotes.id', '=', 'quote_items.quote_id')
        ->join('users', 'users.id', '=', 'quotes.user_id');

        // Apply filters
        if ($request->filled('item')) {
            $query->where('quote_items.item', 'like', '%' . $request->item . '%');
        }

        if ($request->filled('quote_title')) {
            $query->where('quotes.title', 'like', '%' . $request->quote_title . '%');
        }

        if ($request->filled('marketer')) {
            $query->where('users.name', 'like', '%' . $request->marketer . '%');
        }

        if ($request->filled('status')) {
            if ($request->status === 'approved') {
                $query->where('quote_items.approved', 1);
            } elseif ($request->status === 'not_approved') {
                $query->where('quote_items.approved', 0);
            }
        }

        if ($request->filled('date_from')) {
            $query->whereDate('quotes.created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('quotes.created_at', '<=', $request->date_to);
        }

        if ($request->filled('quote_status')) {
            $query->where('quotes.status', $request->quote_status);
        }

        $quoteItems = $query->orderBy('quotes.created_at', 'desc')->paginate(50)->withQueryString();

        return view('product-items.reports', compact('quoteItems'));
    }

    public function exportReports(Request $request)
    {
        $query = QuoteItem::select([
            'quote_items.*',
            'quotes.title as quote_title',
            'quotes.status as quote_status',
            'quotes.created_at as quote_created_at',
            'users.name as marketer_name'
        ])
        ->join('quotes', 'quotes.id', '=', 'quote_items.quote_id')
        ->join('users', 'users.id', '=', 'quotes.user_id');

        // Apply same filters as reports view
        if ($request->filled('item')) {
            $query->where('quote_items.item', 'like', '%' . $request->item . '%');
        }

        if ($request->filled('quote_title')) {
            $query->where('quotes.title', 'like', '%' . $request->quote_title . '%');
        }

        if ($request->filled('marketer')) {
            $query->where('users.name', 'like', '%' . $request->marketer . '%');
        }

        if ($request->filled('status')) {
            if ($request->status === 'approved') {
                $query->where('quote_items.approved', 1);
            } elseif ($request->status === 'not_approved') {
                $query->where('quote_items.approved', 0);
            }
        }

        if ($request->filled('date_from')) {
            $query->whereDate('quotes.created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('quotes.created_at', '<=', $request->date_to);
        }

        if ($request->filled('quote_status')) {
            $query->where('quotes.status', $request->quote_status);
        }

        $data = $query->orderBy('quotes.created_at', 'desc')->get();

        if ($data->isEmpty()) {
            return back()->with('error', 'No data available for export with the selected filters.');
        }

        $totalAmount = $data->sum(function($item) {
            return $item->quantity * $item->price;
        });

        $formattedData = $data->map(function($item) {
            return [
                'Quote Title' => $item->quote_title,
                'Quote Status' => $this->formatStatusForDisplay($item->quote_status),
                'RFQ Processor' => $item->marketer_name,
                'Item Description' => $item->item,
                'Unit Pack' => $item->unit_pack ?? 'N/A',
                'Quantity' => $item->quantity,
                'Unit Price' => $item->price,
                'Total' => $item->quantity * $item->price,
                'VAT Amount' => $item->vat_amount ?? 0,
                'Lead Time' => $item->lead_time ?? 'N/A',
                'Item Status' => $item->approved ? 'Accepted' : 'Rejected',
                'Date' => \Carbon\Carbon::parse($item->quote_created_at)->format('d/m/Y'),
            ];
        });

        // Add total row
        $formattedData->push([
            'Quote Title' => '',
            'Quote Status' => '',
            'RFQ Processor' => '',
            'Item Description' => '',
            'Unit Pack' => '',
            'Quantity' => '',
            'Unit Price' => 'TOTAL',
            'Total' => $totalAmount,
            'VAT Amount' => '',
            'Lead Time' => '',
            'Item Status' => '',
            'Date' => '',
        ]);

        $format = $request->input('format', 'excel');
        $filename = 'product_reports_' . now()->format('Y-m-d');

        if ($format === 'csv') {
            return response($this->arrayToCsv($formattedData->toArray()))
                ->header('Content-Type', 'text/csv')
                ->header('Content-Disposition', "attachment; filename=\"$filename.csv\"");
        }

        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\DataExport($formattedData->toArray()),
            $filename . '.xlsx'
        );
    }

    private function formatStatusForDisplay($status)
    {
        return match($status) {
            'pending_manager' => 'Pending Sarah',
            'pending_customer' => 'Awaiting Customer Response',
            'pending_finance' => 'Work in Progress',
            'completed' => 'Completed',
            'rejected' => 'Rejected',
            default => ucwords(str_replace('_', ' ', $status))
        };
    }


}
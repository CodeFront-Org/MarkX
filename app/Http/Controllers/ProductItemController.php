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

    public function show($itemName)
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

        // Get quote history
        $quoteHistory = DB::table('quotes as q')
            ->select(
                'q.id as quote_id',
                'q.reference',
                'q.title',
                'q.created_at',
                'qi.quantity',
                'qi.price',
                'qi.approved',
                'qi.comment',
                DB::raw('qi.quantity * qi.price as amount')
            )
            ->join('quote_items as qi', 'q.id', '=', 'qi.quote_id')
            ->where('qi.item', $itemName)
            ->orderBy('q.created_at', 'desc')
            ->get();

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
}
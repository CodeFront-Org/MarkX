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
        $query = QuoteItem::query()            ->select([
                'quote_items.item',
                'quote_items.id',
                DB::raw('COUNT(DISTINCT quote_items.quote_id) as quote_count'),
                DB::raw('GROUP_CONCAT(DISTINCT COALESCE(quotes.reference, quotes.title)) as quote_titles'),
                DB::raw('SUM(quote_items.quantity) as total_quantity'),
                DB::raw('AVG(quote_items.price) as avg_price'),
                DB::raw('SUM(quote_items.quantity * quote_items.price) as total_value'),
                DB::raw('SUM(CASE WHEN quote_items.approved = 1 THEN 1 ELSE 0 END) * 100.0 / COUNT(*) as success_rate'),
                DB::raw('GROUP_CONCAT(DISTINCT users.name) as marketers'),
                DB::raw('MIN(CASE WHEN quote_items.approved = 0 THEN 1 ELSE 0 END) as has_pending'),
                DB::raw('MIN(quote_items.comment) as latest_comment')
            ])
            ->with(['quote'])  // Eager load quote relationship
            ->leftJoin('quotes', 'quotes.id', '=', 'quote_items.quote_id')
            ->leftJoin('users', 'users.id', '=', 'quotes.user_id')
            ->groupBy('quote_items.item', 'quote_items.id');

        if (Auth::user()->role !== 'manager') {
            $query->where('quotes.user_id', Auth::id());
        }

        // Apply search filters
        if ($request->filled('item')) {
            $query->having('item', 'like', '%' . $request->item . '%');
        }

        if ($request->filled('min_quantity')) {
            $query->having('total_quantity', '>=', $request->min_quantity);
        }

        if ($request->filled('max_quantity')) {
            $query->having('total_quantity', '<=', $request->max_quantity);
        }

        if ($request->filled('min_price')) {
            $query->having('avg_price', '>=', $request->min_price);
        }

        if ($request->filled('max_price')) {
            $query->having('avg_price', '<=', $request->max_price);
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

        $items = $query->latest('total_value')
            ->paginate(10)
            ->withQueryString();

        // Add quote history for each item
        foreach ($items as $item) {
            $item->quote_history = DB::table('quotes as q')
                ->select(
                    'q.id as quote_id',
                    'q.reference',
                    'q.title',
                    'q.created_at',
                    'qi.quantity',
                    'qi.price',
                    'qi.approved',
                    DB::raw('qi.quantity * qi.price as amount')
                )
                ->join('quote_items as qi', 'q.id', '=', 'qi.quote_id')
                ->where('qi.item', $item->item)
                ->orderBy('q.created_at', 'desc')
                ->get();
        }

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
        $quotes = Quote::when(Auth::user()->role !== 'manager', function($query) {
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

    public function destroy(QuoteItem $item)
    {
        $item->delete();

        return redirect()->route('product-items.index')
            ->with('success', 'Product item deleted successfully.');
    }
}
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
                'quote_items.*',
                'quotes.user_id',
                'quotes.title as quote_title',
                DB::raw('(
                    SELECT COUNT(DISTINCT i.id) 
                    FROM invoices i 
                    WHERE i.quote_id = quotes.id
                ) as invoice_count')
            ])
            ->join('quotes', 'quotes.id', '=', 'quote_items.quote_id')
            ->withCount([
                'quote as quotes_count' => function($query) {
                    $query->select(DB::raw('COUNT(DISTINCT quotes.id)'));
                }
            ]);

        // Calculate success rate (items that made it to invoices)
        $query->addSelect(DB::raw('
            CASE 
                WHEN COUNT(DISTINCT quotes.id) > 0 
                THEN (COUNT(DISTINCT invoices.id) * 100.0 / COUNT(DISTINCT quotes.id)) 
                ELSE 0 
            END as success_rate'
        ))
        ->leftJoin('invoices', 'quotes.id', '=', 'invoices.quote_id')
        ->groupBy([
            'quote_items.id',
            'quote_items.quote_id',
            'quote_items.item',
            'quote_items.quantity',
            'quote_items.price',
            'quote_items.approved',
            'quote_items.created_at',
            'quote_items.updated_at',
            'quotes.user_id',
            'quotes.title'
        ]);

        if (Auth::user()->role !== 'manager') {
            $query->where('quotes.user_id', Auth::id());
        }

        // Apply search filters
        if ($request->filled('item')) {
            $query->where('item', 'like', '%' . $request->item . '%');
        }

        if ($request->filled('min_quantity')) {
            $query->where('quantity', '>=', $request->min_quantity);
        }

        if ($request->filled('max_quantity')) {
            $query->where('quantity', '<=', $request->max_quantity);
        }

        if ($request->filled('min_price')) {
            $query->where('price', '>=', $request->min_price);
        }

        if ($request->filled('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }

        if ($request->filled('marketer')) {
            $query->whereHas('quote.user', function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->marketer . '%')
                    ->orWhere('email', 'like', '%' . $request->marketer . '%');
            });
        }

        if ($request->filled('approved')) {
            $query->where('approved', $request->approved === 'true');
        }

        $items = $query->with(['quote.user'])
            ->latest()
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
        $quotes = Quote::when(Auth::user()->role !== 'manager', function($query) {
            return $query->where('user_id', Auth::id());
        })
        ->with('invoice')
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
            'quote_id' => 'required|exists:quotes,id'
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
            'price' => 'required|numeric|min:0'
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
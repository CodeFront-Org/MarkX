<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use App\Models\ProductItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SupplierController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }
    
    /**
     * Display a listing of the suppliers.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $query = Supplier::query();
        
        // Apply search filters
        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('contact_person', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%');
            });
        }
        
        $suppliers = $query->withCount('products')
            ->with('updatedByUser')
            ->latest()
            ->paginate(10)
            ->withQueryString();
            
        return view('suppliers.index', compact('suppliers'));
    }

    /**
     * Show the form for creating a new supplier.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('suppliers.create');
    }

    /**
     * Store a newly created supplier in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'notes' => 'nullable|string|max:1000',
        ]);
        
        $supplier = Supplier::create([
            ...$validated,
            'updated_by' => Auth::id(),
        ]);
        
        return redirect()->route('suppliers.show', $supplier)
            ->with('success', 'Supplier created successfully.');
    }

    /**
     * Display the specified supplier.
     *
     * @param  \App\Models\Supplier  $supplier
     * @return \Illuminate\Http\Response
     */
    public function show(Supplier $supplier)
    {
        $supplier->load(['products' => function($query) {
            $query->withPivot('price', 'supplier_product_code', 'notes', 'updated_by', 'created_at', 'updated_at');
        }, 'products.suppliers', 'updatedByUser']);
        
        return view('suppliers.show', compact('supplier'));
    }

    /**
     * Show the form for editing the specified supplier.
     *
     * @param  \App\Models\Supplier  $supplier
     * @return \Illuminate\Http\Response
     */
    public function edit(Supplier $supplier)
    {
        return view('suppliers.edit', compact('supplier'));
    }

    /**
     * Update the specified supplier in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Supplier  $supplier
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Supplier $supplier)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'notes' => 'nullable|string|max:1000',
        ]);
        
        $supplier->update([
            ...$validated,
            'updated_by' => Auth::id(),
        ]);
        
        return redirect()->route('suppliers.show', $supplier)
            ->with('success', 'Supplier updated successfully.');
    }

    /**
     * Remove the specified supplier from storage.
     *
     * @param  \App\Models\Supplier  $supplier
     * @return \Illuminate\Http\Response
     */
    public function destroy(Supplier $supplier)
    {
        $supplier->delete();
        
        return redirect()->route('suppliers.index')
            ->with('success', 'Supplier deleted successfully.');
    }
    
    /**
     * Show the form for adding a product to a supplier.
     *
     * @param  \App\Models\Supplier  $supplier
     * @return \Illuminate\Http\Response
     */
    public function addProduct(Supplier $supplier)
    {
        $products = ProductItem::all();
        return view('suppliers.add_product', compact('supplier', 'products'));
    }
    
    /**
     * Attach a product to a supplier.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Supplier  $supplier
     * @return \Illuminate\Http\Response
     */
    public function attachProduct(Request $request, Supplier $supplier)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:product_items,id',
            'price' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:1000',
        ]);
        
        // Check if product is already attached
        $exists = $supplier->products()->where('product_item_id', $validated['product_id'])->exists();
        
        if ($exists) {
            return back()->with('error', 'This product is already associated with this supplier.');
        }
        
        $supplier->products()->attach($validated['product_id'], [
            'price' => $validated['price'],
            'notes' => $validated['notes'],
            'updated_by' => Auth::id(),
        ]);
        
        return redirect()->route('suppliers.show', $supplier)
            ->with('success', 'Product added to supplier successfully.');
    }
    
    /**
     * Show the form for editing a supplier's product.
     *
     * @param  \App\Models\Supplier  $supplier
     * @param  \App\Models\ProductItem  $product
     * @return \Illuminate\Http\Response
     */
    public function editProduct(Supplier $supplier, ProductItem $product)
    {
        $pivotData = DB::table('supplier_product')
            ->where('supplier_id', $supplier->id)
            ->where('product_item_id', $product->id)
            ->first();
            
        if (!$pivotData) {
            return redirect()->route('suppliers.show', $supplier)
                ->with('error', 'This product is not associated with this supplier.');
        }
        
        return view('suppliers.edit_product', compact('supplier', 'product', 'pivotData'));
    }
    
    /**
     * Update a supplier's product.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Supplier  $supplier
     * @param  \App\Models\ProductItem  $product
     * @return \Illuminate\Http\Response
     */
    public function updateProduct(Request $request, Supplier $supplier, ProductItem $product)
    {
        $validated = $request->validate([
            'price' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:1000',
        ]);
        
        $supplier->products()->updateExistingPivot($product->id, [
            'price' => $validated['price'],
            'notes' => $validated['notes'],
            'updated_by' => Auth::id(),
        ]);
        
        return redirect()->route('suppliers.show', $supplier)
            ->with('success', 'Product information updated successfully.');
    }
    
    /**
     * Detach a product from a supplier.
     *
     * @param  \App\Models\Supplier  $supplier
     * @param  \App\Models\ProductItem  $product
     * @return \Illuminate\Http\Response
     */
    public function detachProduct(Supplier $supplier, ProductItem $product)
    {
        $supplier->products()->detach($product->id);
        
        return redirect()->route('suppliers.show', $supplier)
            ->with('success', 'Product removed from supplier successfully.');
    }
}

@extends('layouts.user_type.auth')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header pb-0 d-flex justify-content-between align-items-center">
                    <h6>Supplier Details</h6>
                    <div>
                        <a href="{{ route('suppliers.edit', $supplier) }}" class="btn bg-gradient-primary">Edit Supplier</a>
                        {{-- <a href="{{ route('suppliers.products.add', $supplier) }}" class="btn bg-gradient-success">Add Product</a> --}}
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p class="text-sm mb-0 text-uppercase font-weight-bold">Name</p>
                            <p class="text-sm">{{ $supplier->name }}</p>
                        </div>
                        <div class="col-md-6">
                            <p class="text-sm mb-0 text-uppercase font-weight-bold">Contact Person</p>
                            <p class="text-sm">{{ $supplier->contact_person ?: 'Not specified' }}</p>
                        </div>
                    </div>
                    <div class="row mt-4">
                        <div class="col-md-6">
                            <p class="text-sm mb-0 text-uppercase font-weight-bold">Email</p>
                            <p class="text-sm">{{ $supplier->email ?: 'Not specified' }}</p>
                        </div>
                        <div class="col-md-6">
                            <p class="text-sm mb-0 text-uppercase font-weight-bold">Phone</p>
                            <p class="text-sm">{{ $supplier->phone ?: 'Not specified' }}</p>
                        </div>
                    </div>
                    <div class="row mt-4">
                        <div class="col-md-6">
                            <p class="text-sm mb-0 text-uppercase font-weight-bold">Address</p>
                            <p class="text-sm">{{ $supplier->address ?: 'Not specified' }}</p>
                        </div>
                        <div class="col-md-6">
                            <p class="text-sm mb-0 text-uppercase font-weight-bold">Last Updated</p>
                            <p class="text-sm">
                                {{ $supplier->updated_at->format('M d, Y H:i') }}
                                @if($supplier->updatedByUser)
                                by {{ $supplier->updatedByUser->name }}
                                @endif
                            </p>
                        </div>
                    </div>
                    <div class="row mt-4">
                        <div class="col-md-12">
                            <p class="text-sm mb-0 text-uppercase font-weight-bold">Notes</p>
                            <p class="text-sm">{{ $supplier->notes ?: 'No notes available' }}</p>
                        </div>
                    </div>
                    
                    <!-- Products Section -->
                    {{-- <div class="row mt-5">
                        <div class="col-12">
                            <h6 class="mb-3">Products Supplied</h6>
                            <div class="table-responsive">
                                <table class="table align-items-center mb-0">
                                    <thead>
                                        <tr>
                                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Product</th>
                                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Price</th>
                                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Supplier Code</th>
                                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Notes</th>
                                            <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Last Updated</th>
                                            <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($supplier->products as $product)
                                        <tr>
                                            <td>
                                                <div class="d-flex px-2 py-1">
                                                    <div class="d-flex flex-column justify-content-center">
                                                        <h6 class="mb-0 text-sm">{{ $product->name }}</h6>
                                                        <p class="text-xs text-secondary mb-0">{{ Str::limit($product->description, 50) }}</p>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <p class="text-xs font-weight-bold mb-0">
                                                    @if($product->pivot->price)
                                                    KES {{ number_format($product->pivot->price, 2) }}
                                                    @else
                                                    Not specified
                                                    @endif
                                                </p>
                                            </td>
                                            <td>
                                                <p class="text-xs font-weight-bold mb-0">{{ $product->pivot->supplier_product_code ?: 'Not specified' }}</p>
                                            </td>
                                            <td>
                                                <p class="text-xs font-weight-bold mb-0">{{ Str::limit($product->pivot->notes, 50) ?: 'No notes' }}</p>
                                            </td>
                                            <td class="align-middle text-center">
                                                <span class="text-secondary text-xs font-weight-bold">
                                                    {{ \Carbon\Carbon::parse($product->pivot->updated_at)->format('M d, Y H:i') }}
                                                    @if($product->pivot->updated_by)
                                                    <br><small>by {{ App\Models\User::find($product->pivot->updated_by)->name ?? 'Unknown' }}</small>
                                                    @endif
                                                </span>
                                            </td>
                                            <td class="align-middle text-center">
                                                <a href="{{ route('suppliers.products.edit', [$supplier, $product]) }}" class="btn btn-link text-dark px-3 mb-0">
                                                    <i class="fas fa-pencil-alt text-dark me-2"></i>Edit
                                                </a>
                                                <form action="{{ route('suppliers.products.detach', [$supplier, $product]) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-link text-danger px-3 mb-0" onclick="return confirm('Are you sure you want to remove this product from this supplier?')">
                                                        <i class="fas fa-times text-danger me-2"></i>Remove
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="6" class="text-center py-4">No products associated with this supplier</td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div> --}}
                    
                    <div class="row mt-4">
                        <div class="col-md-12">
                            <a href="{{ route('suppliers.index') }}" class="btn bg-gradient-secondary">Back to Suppliers</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 
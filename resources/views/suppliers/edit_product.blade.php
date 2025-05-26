@extends('layouts.user_type.auth')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header pb-0">
                    <h6>Edit Product Details for {{ $supplier->name }}</h6>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <h5 class="text-secondary">{{ $product->name }}</h5>
                            <p>{{ $product->description }}</p>
                        </div>
                    </div>
                    <form method="POST" action="{{ route('suppliers.products.update', [$supplier, $product]) }}">
                        @csrf
                        @method('PUT')
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="price" class="form-control-label">Price (KES)</label>
                                    <input class="form-control @error('price') is-invalid @enderror" type="number" step="0.01" min="0"
                                        id="price" name="price" value="{{ old('price', $pivotData->price) }}">
                                    @error('price')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="supplier_product_code" class="form-control-label">Supplier Product Code</label>
                                    <div class="input-group">
                                        <input class="form-control" type="text" 
                                            value="{{ $pivotData->supplier_product_code }}" disabled>
                                        <div class="input-group-append">
                                            <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                        </div>
                                    </div>
                                    <small class="text-muted">This code is auto-generated and cannot be changed.</small>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="notes" class="form-control-label">Notes</label>
                                    <textarea class="form-control @error('notes') is-invalid @enderror" id="notes" name="notes" rows="3">{{ old('notes', $pivotData->notes) }}</textarea>
                                    @error('notes')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        <div class="row mt-4">
                            <div class="col-md-12">
                                <button type="submit" class="btn bg-gradient-primary">Update Product Details</button>
                                <a href="{{ route('suppliers.show', $supplier) }}" class="btn bg-gradient-secondary">Cancel</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 
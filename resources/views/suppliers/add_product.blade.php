@extends('layouts.user_type.auth')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header pb-0">
                    <h6>Add Product to {{ $supplier->name }}</h6>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('suppliers.products.attach', $supplier) }}">
                        @csrf
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="product_id" class="form-control-label">Product <span class="text-danger">*</span></label>
                                    <select class="form-control @error('product_id') is-invalid @enderror" id="product_id" name="product_id" required>
                                        <option value="">Select a product</option>
                                        @foreach($products as $product)
                                        <option value="{{ $product->id }}" {{ old('product_id') == $product->id ? 'selected' : '' }}>
                                            {{ $product->name }} - {{ $product->description }}
                                        </option>
                                        @endforeach
                                    </select>
                                    @error('product_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="price" class="form-control-label">Price (KES)</label>
                                    <input class="form-control @error('price') is-invalid @enderror" type="number" step="0.01" min="0"
                                        id="price" name="price" value="{{ old('price') }}">
                                    @error('price')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="supplier_product_code" class="form-control-label">Supplier Product Code</label>
                                    <div class="input-group">
                                        <input class="form-control" type="text" value="Will be auto-generated" disabled>
                                        <div class="input-group-append">
                                            <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                        </div>
                                    </div>
                                    <small class="text-muted">This code will be automatically generated when you save.</small>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="notes" class="form-control-label">Notes</label>
                                    <textarea class="form-control @error('notes') is-invalid @enderror" id="notes" name="notes" rows="3">{{ old('notes') }}</textarea>
                                    @error('notes')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        <div class="row mt-4">
                            <div class="col-md-12">
                                <button type="submit" class="btn bg-gradient-primary">Add Product</button>
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

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize select2 for better product selection
        if (typeof jQuery != 'undefined' && typeof $.fn.select2 != 'undefined') {
            $('#product_id').select2({
                theme: 'bootstrap-5',
                placeholder: 'Select a product',
                allowClear: true
            });
        }
    });
</script>
@endpush 
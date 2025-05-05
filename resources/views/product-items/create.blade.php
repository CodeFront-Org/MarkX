@extends('layouts.user_type.auth')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header pb-0">
                    <h6>Create Product Item</h6>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('product-items.store') }}" id="create-form">
                        @csrf
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label class="form-control-label">Quote</label>
                                    <select name="quote_id" class="form-control" required>
                                        <option value="">Select Quote</option>
                                        @foreach($quotes as $quote)
                                            <option value="{{ $quote->id }}" {{ old('quote_id') == $quote->id ? 'selected' : '' }}>
                                                {{ $quote->title }} ({{ $quote->invoice ? 'Invoiced' : 'Not Invoiced' }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('quote_id')
                                        <p class="text-danger text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-control-label">Item Description</label>
                                    <input type="text" name="item" class="form-control" value="{{ old('item') }}" required>
                                    @error('item')
                                        <p class="text-danger text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="form-control-label">Quantity</label>
                                    <input type="number" name="quantity" class="form-control" value="{{ old('quantity', 1) }}" required min="1">
                                    @error('quantity')
                                        <p class="text-danger text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="form-control-label">Unit Price</label>
                                    <input type="number" step="0.01" name="price" class="form-control" value="{{ old('price') }}" required min="0">
                                    @error('price')
                                        <p class="text-danger text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        <div class="row mt-4">
                            <div class="col-12">
                                <button type="submit" class="btn bg-gradient-primary">Create Product Item</button>
                                <a href="{{ route('product-items.index') }}" class="btn bg-gradient-secondary">Cancel</a>
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
    document.getElementById('create-form').addEventListener('submit', function(e) {
        const quantity = parseInt(this.querySelector('[name="quantity"]').value);
        const price = parseFloat(this.querySelector('[name="price"]').value);

        if (quantity <= 0) {
            e.preventDefault();
            alert('Quantity must be greater than 0');
        }

        if (price < 0) {
            e.preventDefault();
            alert('Price cannot be negative');
        }
    });
</script>
@endpush
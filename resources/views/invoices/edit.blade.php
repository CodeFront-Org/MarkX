@extends('layouts.user_type.auth')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header pb-0">
                    <h6>Edit Invoice #{{ $invoice->invoice_number }}</h6>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('invoices.update', $invoice) }}">
                        @csrf
                        @method('PUT')
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="due_date" class="form-control-label">Due Date</label>
                                    <input class="form-control @error('due_date') is-invalid @enderror" type="date" 
                                        id="due_date" name="due_date" 
                                        value="{{ old('due_date', $invoice->due_date->format('Y-m-d')) }}" required
                                        min="{{ date('Y-m-d', strtotime('+1 day')) }}">
                                    @error('due_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <button type="submit" class="btn bg-gradient-primary">Update Invoice</button>
                                <a href="{{ route('invoices.show', $invoice) }}" class="btn bg-gradient-secondary">Cancel</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@extends('layouts.user_type.auth')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header pb-0">
                    <h6>Generate Invoice from Quote</h6>
                </div>
                <div class="card-body">
                    @if($quote)
                    <form method="POST" action="{{ route('invoices.store') }}">
                        @csrf
                        <input type="hidden" name="quote" value="{{ $quote->id }}">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-control-label">Quote Title</label>
                                    <p class="form-control-static">{{ $quote->title }}</p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-control-label">Amount</label>
                                    <p class="form-control-static">${{ number_format($quote->amount, 2) }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-md-12">
                                <button type="submit" class="btn bg-gradient-primary">Generate Invoice</button>
                                <a href="{{ route('quotes.show', $quote) }}" class="btn bg-gradient-secondary">Cancel</a>
                            </div>
                        </div>
                    </form>
                    @else
                    <div class="alert alert-danger">
                        No quote selected for invoice generation.
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
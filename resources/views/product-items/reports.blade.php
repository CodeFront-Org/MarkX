@extends('layouts.user_type.auth')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header pb-0">
                    <h6>Product Reports - All Quoted Items</h6>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('product-reports.index') }}" class="row g-3 mb-3">
                        <div class="col-md-2">
                            <input type="text" class="form-control" name="item" placeholder="Search item..." value="{{ request('item') }}">
                        </div>
                        <div class="col-md-2">
                            <input type="text" class="form-control" name="quote_title" placeholder="Search quote title..." value="{{ request('quote_title') }}">
                        </div>
                        <div class="col-md-2">
                            <input type="text" class="form-control" name="marketer" placeholder="Search marketer..." value="{{ request('marketer') }}">
                        </div>
                        <div class="col-md-2">
                            <select class="form-control" name="status">
                                <option value="">All Status</option>
                                <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approved</option>
                                <option value="not_approved" {{ request('status') === 'not_approved' ? 'selected' : '' }}>Rejected</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <input type="date" class="form-control" name="date_from" value="{{ request('date_from') }}" placeholder="From date">
                        </div>
                        <div class="col-md-2">
                            <input type="date" class="form-control" name="date_to" value="{{ request('date_to') }}" placeholder="To date">
                        </div>
                        <div class="col-md-12">
                            <button type="submit" class="btn btn-primary btn-sm">Filter</button>
                            <a href="{{ route('product-reports.index') }}" class="btn btn-outline-secondary btn-sm">Clear</a>
                        </div>
                    </form>
                </div>
                <div class="px-0 pt-0 pb-2">
                    <div class="table-responsive p-0">
                        <table class="table align-items-center mb-0">
                            <thead>
                                <tr>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Quote Title</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Marketer</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Item Description</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Unit Pack</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Quantity</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Unit Price</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Total</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">VAT Amount</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Lead Time</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($quoteItems as $item)
                                <tr>
                                    <td class="text-sm font-weight-normal">
                                        <a href="{{ route('quotes.show', $item->quote_id) }}" class="text-primary">
                                            {{ $item->quote_title }}
                                        </a>
                                    </td>
                                    <td class="text-sm font-weight-normal">{{ $item->marketer_name }}</td>
                                    <td class="text-sm font-weight-normal">{{ $item->item }}</td>
                                    <td class="text-sm font-weight-normal">{{ $item->unit_pack ?? 'N/A' }}</td>
                                    <td class="text-sm font-weight-normal">{{ number_format($item->quantity) }}</td>
                                    <td class="text-sm font-weight-normal">KES {{ number_format($item->price, 2) }}</td>
                                    <td class="text-sm font-weight-normal">KES {{ number_format($item->quantity * $item->price, 2) }}</td>
                                    <td class="text-sm font-weight-normal">KES {{ number_format($item->vat_amount ?? 0, 2) }}</td>
                                    <td class="text-sm font-weight-normal">{{ $item->lead_time ?? 'In-stock' }}</td>
                                    <td>
                                        @if($item->approved)
                                            <span class="badge badge-sm bg-gradient-success">Approved</span>
                                        @else
                                            <span class="badge badge-sm bg-gradient-danger">Rejected</span>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="10" class="text-center py-4">No quote items found.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="d-flex justify-content-center mt-3">
                        {{ $quoteItems->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@extends('layouts.user_type.auth')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header pb-0 d-flex justify-content-between align-items-center">
                    <h6>Quote Details</h6>
                    <div>
                        @if($quote->status === 'pending')
                            @if(auth()->id() === $quote->user_id)
                                <form action="{{ route('quotes.approve', $quote) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn bg-gradient-success mx-1">Approve</button>
                                </form>
                                <form action="{{ route('quotes.reject', $quote) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn bg-gradient-danger mx-1">Reject</button>
                                </form>
                        
                                <a href="{{ route('quotes.edit', $quote) }}" class="btn bg-gradient-info mx-1">Edit</a>
                            @endif
                        @endif
                        @if($quote->status === 'approved' && !$quote->invoice && auth()->user()->role !== 'manager && auth()->id() === $quote->user_id)')
                            <form action="{{ route('quotes.convert', $quote) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn bg-gradient-primary mx-1">Convert to Invoice</button>
                            </form>
                        @endif
                        <a href="{{ route('quotes.download', $quote) }}" class="btn bg-gradient-dark mx-1" target="_blank">
                            <i class="fas fa-file-pdf me-2"></i> Download PDF
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p class="text-sm mb-0 text-uppercase font-weight-bold">Title</p>
                            <p class="text-sm">{{ $quote->title }}</p>
                        </div>
                        <div class="col-md-6">
                            <p class="text-sm mb-0 text-uppercase font-weight-bold">Total Amount</p>
                            <p class="text-sm">KES {{ number_format($quote->amount, 2) }}</p>
                        </div>
                    </div>
                    <div class="row mt-4">
                        <div class="col-md-6">
                            <p class="text-sm mb-0 text-uppercase font-weight-bold">Status</p>
                            <span class="badge badge-sm bg-gradient-{{ $quote->status === 'approved' ? 'success' : ($quote->status === 'pending' ? 'info' : ($quote->status === 'converted' ? 'primary' : 'danger')) }}">
                                {{ ucfirst($quote->status) }}
                            </span>
                        </div>
                        <div class="col-md-6">
                            <p class="text-sm mb-0 text-uppercase font-weight-bold">Valid Until</p>
                            <p class="text-sm">{{ $quote->valid_until->format('M d, Y') }}</p>
                        </div>
                    </div>
                    <div class="row mt-4">
                        <div class="col-md-12">
                            <p class="text-sm mb-0 text-uppercase font-weight-bold">Description</p>
                            <p class="text-sm">{{ $quote->description }}</p>
                        </div>
                    </div>
                    <div class="row mt-4">
                        <div class="col-12">
                            <p class="text-sm mb-0 text-uppercase font-weight-bold">Quote Items</p>
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Item Description</th>
                                            <th>Quantity</th>
                                            <th>Unit Price</th>
                                            <th>Subtotal</th>
                                            @if($quote->status === 'pending' && auth()->user()->role === 'manager')
                                            <th>Approve</th>
                                            @elseif($quote->status !== 'pending')
                                            <th>Status</th>
                                            @endif
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($quote->items as $item)
                                        <tr>
                                            <td>{{ $item->item }}</td>
                                            <td>{{ $item->quantity }}</td>
                                            <td>KES {{ number_format($item->price, 2) }}</td>
                                            <td>KES {{ number_format($item->total, 2) }}</td>
                                            @if($quote->status === 'pending' && auth()->user()->role === 'manager')
                                            <td>
                                                <div class="form-check">
                                                    <input type="checkbox" class="form-check-input item-approval" 
                                                        data-item-id="{{ $item->id }}" 
                                                        {{ $item->approved ? 'checked' : '' }}>
                                                </div>
                                            </td>
                                            @elseif($quote->status !== 'pending')
                                            <td>
                                                <span class="badge badge-sm bg-gradient-{{ $item->approved ? 'success' : 'secondary' }}">
                                                    {{ $item->approved ? 'Approved' : 'Not Approved' }}
                                                </span>
                                            </td>
                                            @endif
                                        </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <td colspan="3" class="text-end"><strong>Total Amount:</strong></td>
                                            <td colspan="2"><strong>KES {{ number_format($quote->amount, 2) }}</strong></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                    @if($quote->invoice)
                    <div class="row mt-4">
                        <div class="col-md-12">
                            <p class="text-sm mb-0 text-uppercase font-weight-bold">Converted to Invoice</p>
                            <p class="text-sm">
                                <a href="{{ route('invoices.show', $quote->invoice) }}" class="text-primary">
                                    Invoice #{{ $quote->invoice->invoice_number }}
                                </a>
                            </p>
                        </div>
                    </div>
                    @endif
                    <div class="row mt-4">
                        <div class="col-md-12">
                            <a href="{{ route('quotes.index') }}" class="btn bg-gradient-secondary">Back to Quotes</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@if($quote->status === 'pending' && auth()->user()->role === 'manager')
@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const checkboxes = document.querySelectorAll('.item-approval');
        checkboxes.forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                const itemId = this.dataset.itemId;
                fetch(`/quotes/items/${itemId}/toggle-approval`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                }).then(response => {
                    if (!response.ok) {
                        this.checked = !this.checked;
                        alert('Failed to update item approval status');
                    }
                }).catch(error => {
                    this.checked = !this.checked;
                    alert('Failed to update item approval status');
                });
            });
        });
    });
</script>
@endpush
@endif

@endsection
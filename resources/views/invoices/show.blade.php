@extends('layouts.user_type.auth')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header pb-0 d-flex justify-content-between align-items-center">
                    <h6>Invoice #{{ $invoice->invoice_number }}</h6>
                    <div class="btn-group">
                        @if($invoice->status === 'draft' && auth()->id() === $invoice->user_id)
                            <form action="{{ route('invoices.send', $invoice) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn bg-gradient-success mx-1">Mark as Final</button>
                            </form>
                            <a href="{{ route('invoices.edit', $invoice) }}" class="btn bg-gradient-info mx-1">Edit Invoice</a>
                        @endif
                        @if(($invoice->status === 'final' || $invoice->status === 'overdue') && auth()->id() === $invoice->user_id)
                            <form action="{{ route('invoices.mark-paid', $invoice) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn bg-gradient-success mx-1">Mark as Paid</button>
                            </form>
                        @endif
                        <a href="{{ route('invoices.download', $invoice) }}" class="btn bg-gradient-dark mx-1" target="_blank">
                            <i class="fas fa-file-pdf me-2"></i> Download PDF
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p class="text-sm mb-0 text-uppercase font-weight-bold">Invoice Number</p>
                            <p class="text-sm">{{ $invoice->invoice_number }}</p>
                        </div>
                        <div class="col-md-6">
                            <p class="text-sm mb-0 text-uppercase font-weight-bold">Amount</p>
                            <p class="text-sm">KES {{ number_format($invoice->amount, 2) }}</p>
                        </div>
                    </div>
                    <div class="row mt-4">
                        <div class="col-md-6">
                            <p class="text-sm mb-0 text-uppercase font-weight-bold">Status</p>
                            <span class="badge badge-sm bg-gradient-{{ $invoice->status === 'paid' ? 'success' : ($invoice->status === 'final' ? 'info' : ($invoice->status === 'overdue' ? 'danger' : 'secondary')) }}">
                                {{ ucfirst($invoice->status) }}
                            </span>
                        </div>
                        <div class="col-md-6">
                            <p class="text-sm mb-0 text-uppercase font-weight-bold">Due Date</p>
                            <p class="text-sm">{{ $invoice->due_date->format('M d, Y') }}</p>
                        </div>
                    </div>
                    @if($invoice->quote)
                    <div class="row mt-4">
                        <div class="col-md-12">
                            <p class="text-sm mb-0 text-uppercase font-weight-bold">Generated From Quote</p>
                            <p class="text-sm">
                                <a href="{{ route('quotes.show', $invoice->quote) }}" class="text-primary">
                                    {{ $invoice->quote->title }}
                                </a>
                            </p>
                        </div>
                    </div>
                    <div class="row mt-4">
                        <div class="col-12">
                            <p class="text-sm mb-0 text-uppercase font-weight-bold">Invoice Items</p>
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Item Description</th>
                                            <th>Quantity</th>
                                            <th>Unit Price</th>
                                            <th>Subtotal</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($invoice->quote->items()->where('approved', true)->get() as $item)
                                        <tr>
                                            <td>{{ $item->item }}</td>
                                            <td>{{ $item->quantity }}</td>
                                            <td>KES {{ number_format($item->price, 2) }}</td>
                                            <td>KES {{ number_format($item->total, 2) }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <td colspan="3" class="text-end"><strong>Total Amount:</strong></td>
                                            <td><strong>KES {{ number_format($invoice->amount, 2) }}</strong></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                    @endif
                    @if($invoice->paid_at)
                    <div class="row mt-4">
                        <div class="col-md-12">
                            <p class="text-sm mb-0 text-uppercase font-weight-bold">Payment Date</p>
                            <p class="text-sm">{{ $invoice->paid_at->format('M d, Y H:i:s') }}</p>
                        </div>
                    </div>
                    @endif
                    <div class="row mt-4">
                        <div class="col-md-12">
                            <a href="{{ route('invoices.index') }}" class="btn bg-gradient-secondary">Back to Invoices</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
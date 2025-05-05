@extends('layouts.user_type.auth')

@section('content')
  <div class="container-fluid py-4">
    <div class="row">
      <div class="col-md-7 mt-4">
        <div class="card">
          <div class="card-header pb-0 px-3">
            <h6 class="mb-0">Your Invoices</h6>
          </div>
          <div class="card-body pt-4 p-3">
            <ul class="list-group">
              @forelse($invoices ?? [] as $invoice)
              <li class="list-group-item border-0 d-flex p-4 mb-2 bg-gray-100 border-radius-lg">
                <div class="d-flex flex-column">
                  <h6 class="mb-3 text-sm">Invoice #{{ $invoice->invoice_number }}</h6>
                  <span class="mb-2 text-xs">Amount: <span class="text-dark font-weight-bold ms-sm-2">KES {{ number_format($invoice->amount, 2) }}</span></span>
                  <span class="mb-2 text-xs">Due Date: <span class="text-dark ms-sm-2 font-weight-bold">{{ $invoice->due_date->format('M d, Y') }}</span></span>
                  <span class="text-xs">Status: <span class="ms-sm-2 font-weight-bold badge badge-sm bg-gradient-{{ $invoice->status === 'paid' ? 'success' : ($invoice->status === 'final' ? 'info' : ($invoice->status === 'overdue' ? 'danger' : 'secondary')) }}">{{ ucfirst($invoice->status) }}</span></span>
                </div>
                <div class="ms-auto text-end">
                  <a class="btn btn-link text-info px-3 mb-0" href="{{ route('invoices.show', $invoice) }}">
                    <i class="fas fa-eye text-info me-2"></i>View
                  </a>
                  @if($invoice->status === 'draft' && auth()->user()->role !== 'manager' && auth()->id() === $invoice->user_id)
                    <a class="btn btn-link text-dark px-3 mb-0" href="{{ route('invoices.edit', $invoice) }}">
                      <i class="fas fa-pencil-alt text-dark me-2"></i>Edit
                    </a>
                  @endif
                </div>
              </li>
              @empty
              <li class="list-group-item border-0 d-flex p-4 mb-2 bg-gray-100 border-radius-lg">
                <div class="d-flex flex-column">
                  <h6 class="mb-3 text-sm">No invoices found</h6>
                </div>
              </li>
              @endforelse
            </ul>
          </div>
        </div>
      </div>
      <div class="col-md-5 mt-4">
        <div class="card h-100 mb-4">
          <div class="card-header pb-0 px-3">
            <div class="row">
              <div class="col-md-6">
                <h6 class="mb-0">Recent Transactions</h6>
              </div>
              <div class="col-md-6 d-flex justify-content-end align-items-center">
                <i class="far fa-calendar-alt me-2"></i>
                <small>Last 30 days</small>
              </div>
            </div>
          </div>
          <div class="card-body pt-4 p-3">
            <h6 class="text-uppercase text-body text-xs font-weight-bolder mb-3">Latest</h6>
            <ul class="list-group">
              @forelse($recentTransactions ?? [] as $transaction)
              <li class="list-group-item border-0 d-flex justify-content-between ps-0 mb-2 border-radius-lg">
                <div class="d-flex align-items-center">
                  <button class="btn btn-icon-only btn-rounded btn-outline-{{ $transaction->status === 'paid' ? 'success' : 'danger' }} mb-0 me-3 btn-sm d-flex align-items-center justify-content-center">
                    <i class="fas fa-arrow-{{ $transaction->status === 'paid' ? 'up' : 'down' }}"></i>
                  </button>
                  <div class="d-flex flex-column">
                    <h6 class="mb-1 text-dark text-sm">Invoice #{{ $transaction->invoice_number }}</h6>
                    <span class="text-xs">{{ $transaction->paid_at ? $transaction->paid_at->format('M d, Y, h:i A') : $transaction->created_at->format('M d, Y, h:i A') }}</span>
                  </div>
                </div>
                <div class="d-flex align-items-center text-{{ $transaction->status === 'paid' ? 'success' : 'danger' }} text-gradient text-sm font-weight-bold">
                  {{ $transaction->status === 'paid' ? '+' : '-' }} KES {{ number_format($transaction->amount, 2) }}
                </div>
              </li>
              @empty
              <li class="list-group-item border-0 d-flex justify-content-between ps-0 mb-2 border-radius-lg">
                <div class="d-flex align-items-center">
                  <div class="d-flex flex-column">
                    <h6 class="mb-1 text-dark text-sm">No recent transactions</h6>
                  </div>
                </div>
              </li>
              @endforelse
            </ul>
          </div>
        </div>
      </div>
    </div>
  </div>
@endsection


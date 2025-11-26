@extends('layouts.user_type.auth')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header pb-0 d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">Product Reports - All Quoted Items</h6>
                    <div class="dropdown">
                        <button class="btn btn-success btn-sm" type="button" id="exportDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                             Export ↓
                        </button>
                        <ul class="dropdown-menu" aria-labelledby="exportDropdown">
                            <li>
                                <a class="dropdown-item" href="{{ route('product-reports.export', array_merge(request()->all(), ['format' => 'excel'])) }}">
                                    📊 Excel
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="{{ route('product-reports.export', array_merge(request()->all(), ['format' => 'csv'])) }}">
                                    📄 CSV
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
                <div class="card-body pb-2">
                    <div class="bg-gradient-light border-radius-lg p-3 mb-3">
                        <h6 class="text-dark text-sm mb-3">
                            <i class="fas fa-filter me-2"></i>Filter Product Items
                        </h6>
                        <form method="GET" action="{{ route('product-reports.index') }}">
                            <div class="row g-3">
                                <div class="col-lg-3 col-md-6">
                                    <label class="form-label text-xs font-weight-bold mb-1">
                                        <i class="fas fa-box text-primary me-1"></i>Item Description
                                    </label>
                                    <input type="text" class="form-control form-control-sm" name="item" placeholder="Search item..." value="{{ request('item') }}">
                                </div>
                                <div class="col-lg-3 col-md-6">
                                    <label class="form-label text-xs font-weight-bold mb-1">
                                        <i class="fas fa-file-alt text-info me-1"></i>Quote Title
                                    </label>
                                    <input type="text" class="form-control form-control-sm" name="quote_title" placeholder="Search quote..." value="{{ request('quote_title') }}">
                                </div>
                                <div class="col-lg-3 col-md-6">
                                    <label class="form-label text-xs font-weight-bold mb-1">
                                        <i class="fas fa-user text-success me-1"></i>RFQ Processor
                                    </label>
                                    <input type="text" class="form-control form-control-sm" name="marketer" placeholder="Search processor..." value="{{ request('marketer') }}">
                                </div>
                                <div class="col-lg-3 col-md-6">
                                    <label class="form-label text-xs font-weight-bold mb-1">
                                        <i class="fas fa-check-circle text-warning me-1"></i>Item Status
                                    </label>
                                    <select class="form-select form-select-sm" name="status">
                                        <option value="">All Item Status</option>
                                        <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Accepted</option>
                                        <option value="not_approved" {{ request('status') === 'not_approved' ? 'selected' : '' }}>Rejected</option>
                                    </select>
                                </div>
                            </div>
                            <div class="row g-3 mt-2">
                                <div class="col-lg-3 col-md-6">
                                    <label class="form-label text-xs font-weight-bold mb-1">
                                        <i class="fas fa-tasks text-danger me-1"></i>Quote Status
                                    </label>
                                    <select class="form-select form-select-sm" name="quote_status">
                                        <option value="">All Quote Status</option>
                                        <option value="pending_manager" {{ request('quote_status') === 'pending_manager' ? 'selected' : '' }}>Pending Sarah</option>
                                        <option value="pending_customer" {{ request('quote_status') === 'pending_customer' ? 'selected' : '' }}>Awaiting Customer Response</option>
                                        <option value="pending_finance" {{ request('quote_status') === 'pending_finance' ? 'selected' : '' }}>Work in Progress</option>
                                        <option value="completed" {{ request('quote_status') === 'completed' ? 'selected' : '' }}>Completed</option>
                                        <option value="rejected" {{ request('quote_status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
                                    </select>
                                </div>
                                <div class="col-lg-3 col-md-6">
                                    <label class="form-label text-xs font-weight-bold mb-1">
                                        <i class="fas fa-calendar text-primary me-1"></i>From Date
                                    </label>
                                    <input type="date" class="form-control form-control-sm" name="date_from" value="{{ request('date_from') }}">
                                </div>
                                <div class="col-lg-3 col-md-6">
                                    <label class="form-label text-xs font-weight-bold mb-1">
                                        <i class="fas fa-calendar text-primary me-1"></i>To Date
                                    </label>
                                    <input type="date" class="form-control form-control-sm" name="date_to" value="{{ request('date_to') }}">
                                </div>
                                <div class="col-lg-3 col-md-6 d-flex align-items-end">
                                    <div class="d-flex gap-2 w-100">
                                        <button type="submit" class="btn btn-primary btn-sm flex-grow-1">
                                            <i class="fas fa-search me-1"></i>FILTER
                                        </button>
                                        <a href="{{ route('product-reports.index') }}" class="btn btn-outline-secondary btn-sm flex-grow-1">
                                            <i class="fas fa-redo me-1"></i>RESET
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="px-0 pt-0 pb-2">
                    <div class="table-responsive p-0">
                        <table class="table align-items-center mb-0">
                            <thead>
                                <tr>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Quote Title</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Quote Status</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Marketer</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Item Description</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Unit Pack</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Quantity</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Unit Price</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Total</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">VAT Amount</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Lead Time</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Item Status</th>
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
                                    <td>
                                        <span class="badge badge-sm bg-gradient-{{
                                            $item->quote_status === 'completed' ? 'success' :
                                            ($item->quote_status === 'pending_manager' ? 'info' :
                                            ($item->quote_status === 'pending_customer' ? 'warning' :
                                            ($item->quote_status === 'pending_finance' ? 'primary' : 'danger')))
                                        }}">
                                            @if($item->quote_status === 'pending_manager')
                                                Pending Sarah
                                            @elseif($item->quote_status === 'pending_customer')
                                                Awaiting Customer Response
                                            @elseif($item->quote_status === 'pending_finance')
                                                Work in Progress
                                            @else
                                                {{ ucwords(str_replace('_', ' ', $item->quote_status)) }}
                                            @endif
                                        </span>
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
                                            <span class="badge badge-sm bg-gradient-success">Accepted</span>
                                        @else
                                            <span class="badge badge-sm bg-gradient-danger">Rejected</span>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="11" class="text-center py-4">No quote items found.</td>
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

@extends('layouts.user_type.auth')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header pb-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-0">{{ $item->item }}</h6>
                            <p class="text-sm text-muted mb-0">Product Item Details</p>
                        </div>
                        <a href="{{ route('product-items.index') }}" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-arrow-left me-1"></i>Back to List
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    <!-- Summary Cards -->
                    <div class="row mb-4">
                        <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
                            <div class="card">
                                <div class="card-body p-3">
                                    <div class="row">
                                        <div class="col-8">
                                            <div class="numbers">
                                                <p class="text-sm mb-0 text-capitalize font-weight-bold">Total Quotes</p>
                                                <h5 class="font-weight-bolder mb-0">{{ number_format($item->quote_count) }}</h5>
                                            </div>
                                        </div>
                                        <div class="col-4 text-end">
                                            <div class="icon icon-shape bg-gradient-primary shadow text-center border-radius-md">
                                                <i class="ni ni-paper-diploma text-lg opacity-10" aria-hidden="true"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
                            <div class="card">
                                <div class="card-body p-3">
                                    <div class="row">
                                        <div class="col-8">
                                            <div class="numbers">
                                                <p class="text-sm mb-0 text-capitalize font-weight-bold">Total Quantity</p>
                                                <h5 class="font-weight-bolder mb-0">{{ number_format($item->total_quantity) }}</h5>
                                            </div>
                                        </div>
                                        <div class="col-4 text-end">
                                            <div class="icon icon-shape bg-gradient-info shadow text-center border-radius-md">
                                                <i class="ni ni-box-2 text-lg opacity-10" aria-hidden="true"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
                            <div class="card">
                                <div class="card-body p-3">
                                    <div class="row">
                                        <div class="col-8">
                                            <div class="numbers">
                                                <p class="text-sm mb-0 text-capitalize font-weight-bold">Unit Price</p>
                                                <h5 class="font-weight-bolder mb-0">KES {{ number_format($item->avg_price, 2) }}</h5>
                                            </div>
                                        </div>
                                        <div class="col-4 text-end">
                                            <div class="icon icon-shape bg-gradient-success shadow text-center border-radius-md">
                                                <i class="ni ni-money-coins text-lg opacity-10" aria-hidden="true"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-sm-6">
                            <div class="card">
                                <div class="card-body p-3">
                                    <div class="row">
                                        <div class="col-8">
                                            <div class="numbers">
                                                <p class="text-sm mb-0 text-capitalize font-weight-bold">Success Rate</p>
                                                <h5 class="font-weight-bolder mb-0">{{ number_format($item->success_rate, 1) }}%</h5>
                                            </div>
                                        </div>
                                        <div class="col-4 text-end">
                                            <div class="icon icon-shape bg-gradient-warning shadow text-center border-radius-md">
                                                <i class="ni ni-chart-bar-32 text-lg opacity-10" aria-hidden="true"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Additional Info -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="card h-100">
                                <div class="card-header pb-0">
                                    <h6>Item Information</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-6">
                                            <p class="text-sm mb-2"><strong>Total Value:</strong></p>
                                            <p class="text-lg font-weight-bold text-success mb-3">KES {{ number_format($item->total_value, 2) }}</p>
                                        </div>
                                        <div class="col-6">
                                            <p class="text-sm mb-2"><strong>Status:</strong></p>
                                            <span class="badge bg-gradient-{{ $item->has_pending ? 'warning' : 'success' }} mb-3">
                                                {{ $item->has_pending ? 'PENDING' : 'APPROVED' }}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-12">
                                            <p class="text-sm mb-2"><strong>Marketers:</strong></p>
                                            <p class="text-sm text-muted">{{ $item->marketers }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card h-100">
                                <div class="card-header pb-0">
                                    <h6>Approval Status</h6>
                                </div>
                                <div class="card-body d-flex align-items-center justify-content-center">
                                    <div class="text-center" style="width: 200px; height: 200px;">
                                        <canvas id="approvalChart"></canvas>
                                        <p class="text-sm text-muted mb-0 mt-2">Approved vs Pending</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Quote History -->
                    <div class="card">
                        <div class="card-header pb-0">
                            <div class="d-flex justify-content-between align-items-center">
                                <h6>Quote History</h6>
                                <form method="GET" class="d-flex align-items-center">
                                    <select name="approved_status" class="form-select form-select-sm me-2" style="width: 150px;" onchange="this.form.submit()">
                                        <option value="">All Status</option>
                                        <option value="approved" {{ request('approved_status') === 'approved' ? 'selected' : '' }}>Approved Only</option>
                                        <option value="not_approved" {{ request('approved_status') === 'not_approved' ? 'selected' : '' }}>Not Approved Only</option>
                                    </select>
                                </form>
                            </div>
                        </div>
                        <div class="card-body">
                            @if($quoteHistory->count() > 0)
                            <div class="table-responsive">
                                <table class="table align-items-center mb-0">
                                    <thead>
                                        <tr>
                                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Quote</th>
                                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Marketer</th>
                                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Date</th>
                                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Quantity</th>
                                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Price</th>
                                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Amount</th>
                                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Quote Status</th>
                                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Item Status</th>
                                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Comment</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($quoteHistory as $quote)
                                        <tr>
                                            <td>
                                                <div class="d-flex px-2 py-1">
                                                    <div class="d-flex flex-column justify-content-center">
                                                        <h6 class="mb-0 text-sm">
                                                            <a href="{{ route('quotes.show', $quote->quote_id) }}" class="text-primary">
                                                                {{ $quote->reference }}
                                                            </a>
                                                        </h6>
                                                        <p class="text-xs text-secondary mb-0">{{ Str::limit($quote->title, 30) }}</p>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <p class="text-sm font-weight-bold mb-0">{{ $quote->marketer_name ?: '-' }}</p>
                                            </td>
                                            <td>
                                                <p class="text-sm font-weight-bold mb-0">{{ \Carbon\Carbon::parse($quote->created_at)->format('d M Y') }}</p>
                                            </td>
                                            <td>
                                                <p class="text-sm font-weight-bold mb-0">{{ number_format($quote->quantity) }}</p>
                                            </td>
                                            <td>
                                                <p class="text-sm font-weight-bold mb-0">KES {{ number_format($quote->price, 2) }}</p>
                                            </td>
                                            <td>
                                                <p class="text-sm font-weight-bold mb-0">KES {{ number_format($quote->amount, 2) }}</p>
                                            </td>
                                            <td>
                                                <span class="badge badge-sm bg-gradient-{{ 
                                                    $quote->quote_status === 'completed' ? 'success' : 
                                                    ($quote->quote_status === 'rejected' ? 'danger' : 'warning') 
                                                }}">
                                                    {{ ucwords(str_replace('_', ' ', $quote->quote_status)) }}
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge badge-sm bg-gradient-{{ $quote->approved ? 'success' : 'warning' }}">
                                                    {{ $quote->approved ? 'APPROVED' : 'NOT APPROVED' }}
                                                </span>
                                            </td>
                                            <td>
                                                <p class="text-sm mb-0">{{ $quote->comment ?: '-' }}</p>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            @else
                            <div class="text-center py-4">
                                <i class="fas fa-history fa-3x text-secondary mb-2"></i>
                                <p class="text-secondary">No quote history available for this item.</p>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('approvalChart').getContext('2d');
    new Chart(ctx, {
        type: 'pie',
        data: {
            labels: ['Approved', 'Pending'],
            datasets: [{
                data: [{{ $approvedCount }}, {{ $pendingCount }}],
                backgroundColor: ['#82d616', '#fb6340'],
                borderWidth: 2,
                borderColor: '#fff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        padding: 10,
                        usePointStyle: true,
                        font: {
                            size: 12
                        }
                    }
                }
            }
        }
    });
});
</script>
@endpush

@extends('layouts.user_type.auth')

@section('content')
<div class="container-fluid py-4">
    <!-- User Info Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header pb-0 d-flex justify-content-between align-items-center">
                    <h6>{{ $user->name }} - Detailed Report</h6>
                    <a href="{{ route('reports.index') }}" class="btn btn-secondary btn-sm">Back to Reports</a>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-2">
                            <p class="text-sm mb-0 text-uppercase font-weight-bold">Name</p>
                            <p class="text-sm">{{ $user->name }}</p>
                        </div>
                        <div class="col-md-2">
                            <p class="text-sm mb-0 text-uppercase font-weight-bold">Email</p>
                            <p class="text-sm">{{ $user->email }}</p>
                        </div>
                        <div class="col-md-2">
                            <p class="text-sm mb-0 text-uppercase font-weight-bold">Role</p>
                            <p class="text-sm">{{ ucwords(str_replace('_', ' ', $user->role)) }}</p>
                        </div>
                        <div class="col-md-3">
                            <p class="text-sm mb-0 text-uppercase font-weight-bold">Member Since</p>
                            <p class="text-sm">{{ $user->created_at->format('M d, Y') }}</p>
                        </div>
                        <div class="col-md-3">
                            <p class="text-sm mb-0 text-uppercase font-weight-bold">Last Activity</p>
                            <p class="text-sm">{{ $user->updated_at->format('M d, Y') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Performance Metrics -->
    <div class="row mb-4">
        <div class="col-xl-2 col-sm-6 mb-3">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="flex-grow-1">
                            <p class="text-xs text-uppercase text-muted mb-1 font-weight-bold">Total Quotes</p>
                            <h4 class="font-weight-bold mb-0">{{ $stats->total_quotes }}</h4>
                        </div>
                        <div class="icon icon-shape bg-gradient-primary shadow text-center border-radius-md">
                            <i class="ni ni-paper-diploma text-lg opacity-10" aria-hidden="true"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-sm-6 mb-3">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="flex-grow-1">
                            <p class="text-xs text-uppercase text-muted mb-1 font-weight-bold">Total Value</p>
                            <h5 class="font-weight-bold mb-0">KES {{ number_format($stats->total_amount, 0) }}</h5>
                        </div>
                        <div class="icon icon-shape bg-gradient-warning shadow text-center border-radius-md">
                            <i class="ni ni-money-coins text-lg opacity-10" aria-hidden="true"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-sm-6 mb-3">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="flex-grow-1">
                            <p class="text-xs text-uppercase text-muted mb-1 font-weight-bold">Completed</p>
                            <h4 class="font-weight-bold mb-0 text-success">{{ $stats->completed_quotes }}</h4>
                            <div class="mt-2">
                                <span class="badge badge-sm bg-gradient-success">{{ number_format($stats->success_rate, 1) }}%</span>
                                <p class="text-xs text-muted mb-0 mt-1">KES {{ number_format($stats->completed_amount, 0) }}</p>
                            </div>
                        </div>
                        <div class="icon icon-shape bg-gradient-success shadow text-center border-radius-md">
                            <i class="ni ni-check-bold text-lg opacity-10" aria-hidden="true"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-sm-6 mb-3">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="flex-grow-1">
                            <p class="text-xs text-uppercase text-muted mb-1 font-weight-bold">Pending</p>
                            <h4 class="font-weight-bold mb-0 text-info">{{ $stats->pending_quotes }}</h4>
                            <div class="mt-2">
                                <span class="badge badge-sm bg-gradient-info">{{ $stats->total_quotes > 0 ? round(($stats->pending_quotes / $stats->total_quotes) * 100, 1) : 0 }}%</span>
                                <p class="text-xs text-muted mb-0 mt-1">KES {{ number_format($stats->pending_amount, 0) }}</p>
                            </div>
                        </div>
                        <div class="icon icon-shape bg-gradient-info shadow text-center border-radius-md">
                            <i class="ni ni-time-alarm text-lg opacity-10" aria-hidden="true"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-sm-6 mb-3">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="flex-grow-1">
                            <p class="text-xs text-uppercase text-muted mb-1 font-weight-bold">Rejected</p>
                            <h4 class="font-weight-bold mb-0 text-danger">{{ $stats->rejected_quotes }}</h4>
                            <div class="mt-2">
                                <span class="badge badge-sm bg-gradient-danger">{{ $stats->total_quotes > 0 ? round(($stats->rejected_quotes / $stats->total_quotes) * 100, 1) : 0 }}%</span>
                                <p class="text-xs text-muted mb-0 mt-1">KES {{ number_format($stats->rejected_amount, 0) }}</p>
                            </div>
                        </div>
                        <div class="icon icon-shape bg-gradient-danger shadow text-center border-radius-md">
                            <i class="ni ni-fat-remove text-lg opacity-10" aria-hidden="true"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-sm-6 mb-3">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="flex-grow-1">
                            <p class="text-xs text-uppercase text-muted mb-1 font-weight-bold">Avg Quote Value</p>
                            <h5 class="font-weight-bold mb-0">KES {{ number_format($stats->avg_quote_value, 0) }}</h5>
                        </div>
                        <div class="icon icon-shape bg-gradient-primary shadow text-center border-radius-md">
                            <i class="ni ni-chart-bar-32 text-lg opacity-10" aria-hidden="true"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quotes Table -->
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header pb-0">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="mb-0">All Quotes by {{ $user->name }}</h6>
                    </div>
                    <form method="GET" class="row g-3 mb-3 align-items-end">
                        <div class="col-md-2">
                            <label class="form-label text-xs mb-1">Quote ID</label>
                            <input type="text" name="quote_id" class="form-control form-control-sm" placeholder="Quote ID" value="{{ request('quote_id') }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label text-xs mb-1">Title</label>
                            <input type="text" name="title" class="form-control form-control-sm" placeholder="Title" value="{{ request('title') }}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label text-xs mb-1">Status</label>
                            <select name="status" class="form-select form-select-sm">
                                <option value="">All Status</option>
                                <option value="pending_manager" {{ request('status') == 'pending_manager' ? 'selected' : '' }}>Pending RFQ Approver</option>
                                <option value="pending_customer" {{ request('status') == 'pending_customer' ? 'selected' : '' }}>Pending Customer</option>
                                <option value="pending_finance" {{ request('status') == 'pending_finance' ? 'selected' : '' }}>Pending LPO Admin</option>
                                <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                                <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label text-xs mb-1">From Date</label>
                            <input type="date" name="date_from" class="form-control form-control-sm" value="{{ request('date_from') }}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label text-xs mb-1">To Date</label>
                            <input type="date" name="date_to" class="form-control form-control-sm" value="{{ request('date_to') }}">
                        </div>
                        <div class="col-md-1">
                            <button type="submit" class="btn btn-primary btn-sm w-100">Filter</button>
                        </div>
                    </form>
                </div>
                <div class="card-body px-0 pt-0 pb-2">
                    <div class="table-responsive p-0">
                        <table class="table align-items-center mb-0">
                            <thead>
                                <tr>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Quote ID</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Title</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Amount</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Status</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Created</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($quotes as $quote)
                                    <tr>
                                        <td>
                                            <div class="d-flex px-3 py-1">
                                                <div class="d-flex flex-column justify-content-center">
                                                    <h6 class="mb-0 text-sm">#{{ $quote->id }}</h6>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <p class="text-sm font-weight-bold mb-0">{{ $quote->title }}</p>
                                            <p class="text-xs text-secondary mb-0">{{ Str::limit($quote->description, 50) }}</p>
                                        </td>
                                        <td>
                                            <p class="text-sm font-weight-bold mb-0">KES {{ number_format($quote->amount, 2) }}</p>
                                        </td>
                                        <td>
                                            <span class="badge badge-sm bg-gradient-{{ 
                                                $quote->status === 'completed' ? 'success' : 
                                                ($quote->status === 'pending_manager' ? 'info' : 
                                                ($quote->status === 'pending_customer' ? 'warning' : 
                                                ($quote->status === 'pending_finance' ? 'primary' : 'danger'))) 
                                            }}">
                                                {{ $quote->status === 'pending_manager' ? 'Pending RFQ Approver' :
                                                   ($quote->status === 'pending_customer' ? 'Pending Customer' :
                                                   ($quote->status === 'pending_finance' ? 'Pending LPO Admin' :
                                                   ucwords(str_replace('_', ' ', $quote->status)))) }}
                                            </span>
                                        </td>
                                        <td>
                                            <p class="text-sm font-weight-bold mb-0">{{ $quote->created_at->format('M d, Y') }}</p>
                                            <p class="text-xs text-secondary mb-0">{{ $quote->created_at->format('H:i') }}</p>
                                        </td>
                                        <td>
                                            <a href="{{ route('quotes.show', $quote) }}" class="btn btn-sm btn-info">
                                                <i class="fas fa-eye"></i> View
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center">No quotes found for this user.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if($quotes->hasPages())
                        <div class="d-flex justify-content-center mt-3">
                            {{ $quotes->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
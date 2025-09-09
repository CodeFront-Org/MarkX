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
        <div class="col-xl-2 col-sm-6">
            <div class="card">
                <div class="card-body p-3">
                    <div class="row">
                        <div class="col-8">
                            <div class="numbers">
                                <p class="text-sm mb-0 text-uppercase font-weight-bold">Total Quotes</p>
                                <h5 class="font-weight-bolder mb-0">{{ $stats->total_quotes }}</h5>
                            </div>
                        </div>
                        <div class="col-4 text-end">
                            <div class="icon icon-shape bg-gradient-primary shadow text-center border-radius-md">
                                <i class="ni ni-paper-diploma text-lg opacity-10"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-sm-6">
            <div class="card">
                <div class="card-body p-3">
                    <div class="row">
                        <div class="col-8">
                            <div class="numbers">
                                <p class="text-sm mb-0 text-uppercase font-weight-bold">Total Value</p>
                                <h5 class="font-weight-bolder mb-0">KES {{ number_format($stats->total_amount, 0) }}</h5>
                            </div>
                        </div>
                        <div class="col-4 text-end">
                            <div class="icon icon-shape bg-gradient-success shadow text-center border-radius-md">
                                <i class="ni ni-money-coins text-lg opacity-10"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-sm-6">
            <div class="card">
                <div class="card-body p-3">
                    <div class="row">
                        <div class="col-8">
                            <div class="numbers">
                                <p class="text-sm mb-0 text-uppercase font-weight-bold">Completed</p>
                                <h5 class="font-weight-bolder mb-0">{{ $stats->completed_quotes }}</h5>
                            </div>
                        </div>
                        <div class="col-4 text-end">
                            <div class="icon icon-shape bg-gradient-success shadow text-center border-radius-md">
                                <i class="ni ni-check-bold text-lg opacity-10"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-sm-6">
            <div class="card">
                <div class="card-body p-3">
                    <div class="row">
                        <div class="col-8">
                            <div class="numbers">
                                <p class="text-sm mb-0 text-uppercase font-weight-bold">Pending</p>
                                <h5 class="font-weight-bolder mb-0">{{ $stats->pending_quotes }}</h5>
                            </div>
                        </div>
                        <div class="col-4 text-end">
                            <div class="icon icon-shape bg-gradient-warning shadow text-center border-radius-md">
                                <i class="ni ni-time-alarm text-lg opacity-10"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-sm-6">
            <div class="card">
                <div class="card-body p-3">
                    <div class="row">
                        <div class="col-8">
                            <div class="numbers">
                                <p class="text-sm mb-0 text-uppercase font-weight-bold">Rejected</p>
                                <h5 class="font-weight-bolder mb-0">{{ $stats->rejected_quotes }}</h5>
                            </div>
                        </div>
                        <div class="col-4 text-end">
                            <div class="icon icon-shape bg-gradient-danger shadow text-center border-radius-md">
                                <i class="ni ni-fat-remove text-lg opacity-10"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-sm-6">
            <div class="card">
                <div class="card-body p-3">
                    <div class="row">
                        <div class="col-8">
                            <div class="numbers">
                                <p class="text-sm mb-0 text-uppercase font-weight-bold">Success Rate</p>
                                <h5 class="font-weight-bolder mb-0">{{ number_format($stats->success_rate, 1) }}%</h5>
                            </div>
                        </div>
                        <div class="col-4 text-end">
                            <div class="icon icon-shape bg-gradient-info shadow text-center border-radius-md">
                                <i class="ni ni-chart-bar-32 text-lg opacity-10"></i>
                            </div>
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
                    <h6>All Quotes by {{ $user->name }}</h6>
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
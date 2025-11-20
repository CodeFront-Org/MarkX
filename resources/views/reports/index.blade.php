@extends('layouts.user_type.auth')

@push('css')
<style>
.select2-container--bootstrap-5 .select2-selection--single {
    height: 38px !important;
}
.select2-container--bootstrap-5 .select2-selection--single .select2-selection__rendered {
    line-height: 36px !important;
    font-size: 14px !important;
}
</style>
@endpush

@section('content')
<div class="container-fluid py-2 py-md-4" style="max-width: 100vw; overflow-x: hidden;">
    <!-- Flash Messages -->
    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <span class="alert-text"><strong>Error!</strong> {{ session('error') }}</span>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
    @endif

    @if(session('export_success') || session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <span class="alert-text"><strong>Success!</strong> {{ session('export_success') ?? session('success') }}</span>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
    @endif

    <!-- Performance Overview Card -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header pb-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6>Performance Overview</h6>
                        <form method="GET" class="d-flex flex-wrap align-items-end gap-3">
                            <div class="form-group">
                                <select name="user_filter" class="form-select form-select-sm" style="width: 150px; height: 38px;">
                                    <option value="">All Processors</option>
                                    @foreach($rfq_processors as $processor)
                                        <option value="{{ $processor->id }}" {{ request('user_filter') == $processor->id ? 'selected' : '' }}>
                                            {{ $processor->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="form-label text-xs mb-1">Quote Title</label>
                                <select name="quote_title_filter" id="quote-title-select" class="form-select form-select-sm" style="width: 200px; height: 38px;">
                                    @if(request('quote_title_filter'))
                                        @php
                                            $selectedQuote = \App\Models\Quote::find(request('quote_title_filter'));
                                        @endphp
                                        @if($selectedQuote)
                                            <option value="{{ $selectedQuote->id }}" selected>{{ $selectedQuote->title }}</option>
                                        @endif
                                    @endif
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="form-label text-xs mb-1">From Date</label>
                                <input type="date" name="date_from" class="form-control form-control-sm" value="{{ request('date_from') }}" style="width: 140px; height: 38px;">
                            </div>
                            <div class="form-group">
                                <label class="form-label text-xs mb-1">To Date</label>
                                <input type="date" name="date_to" class="form-control form-control-sm" value="{{ request('date_to') }}" style="width: 140px; height: 38px;">
                            </div>
                            <div class="form-group d-flex gap-2">
                                <button type="submit" class="btn btn-primary btn-sm px-3" style="height: 38px;">FILTER</button>
                                <a href="{{ route('reports.index') }}" class="btn btn-outline-secondary btn-sm px-3" style="height: 38px; line-height: 26px;">RESET</a>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="card-body">
                    <!-- KPI Metrics -->
                    <div class="row mb-4">
                        <div class="col-lg-3 col-md-6 col-12 mb-3">
                            <div class="card shadow-sm border-0 h-100">
                                <div class="card-body p-3">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div class="flex-grow-1">
                                            <p class="text-xs text-uppercase text-muted mb-1 font-weight-bold">Total Quoted</p>
                                            <h4 class="font-weight-bold mb-0">KES {{ number_format($quoteStats->total_quoted_amount, 0) }}</h4>
                                            <div class="mt-2">
                                                <span class="badge badge-sm bg-light text-dark">{{ $quoteStats->total_quotes }} quotes</span>
                                            </div>
                                        </div>
                                        <div class="icon icon-shape bg-gradient-warning shadow text-center border-radius-md">
                                            <i class="ni ni-money-coins text-lg opacity-10" aria-hidden="true"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6 col-12 mb-3">
                            <div class="card shadow-sm border-0 h-100">
                                <div class="card-body p-3">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div class="flex-grow-1">
                                            <p class="text-xs text-uppercase text-muted mb-1 font-weight-bold">Total Awarded</p>
                                            <h4 class="font-weight-bold mb-0 text-success">KES {{ number_format($quoteStats->awarded_amount, 0) }}</h4>
                                            <div class="mt-2">
                                                <span class="badge badge-sm bg-gradient-success">{{ $quoteStats->success_rate }}%</span>
                                                <span class="text-xs text-muted ms-1">{{ $quoteStats->successful_quotes }} quotes</span>
                                            </div>
                                        </div>
                                        <div class="icon icon-shape bg-gradient-success shadow text-center border-radius-md">
                                            <i class="ni ni-check-bold text-lg opacity-10" aria-hidden="true"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6 col-12 mb-3">
                            <div class="card shadow-sm border-0 h-100">
                                <div class="card-body p-3">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div class="flex-grow-1">
                                            <p class="text-xs text-uppercase text-muted mb-1 font-weight-bold">Total Rejected</p>
                                            <h4 class="font-weight-bold mb-0 text-danger">KES {{ number_format($quoteStats->rejected_amount, 0) }}</h4>
                                            <div class="mt-2">
                                                <span class="badge badge-sm bg-gradient-danger">{{ $quoteStats->total_quotes > 0 ? round(($quoteStats->rejected_quotes / $quoteStats->total_quotes) * 100, 1) : 0 }}%</span>
                                                <span class="text-xs text-muted ms-1">{{ $quoteStats->rejected_quotes }} quotes</span>
                                            </div>
                                        </div>
                                        <div class="icon icon-shape bg-gradient-danger shadow text-center border-radius-md">
                                            <i class="ni ni-fat-remove text-lg opacity-10" aria-hidden="true"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6 col-12 mb-3">
                            <div class="card shadow-sm border-0 h-100">
                                <div class="card-body p-3">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div class="flex-grow-1">
                                            <p class="text-xs text-uppercase text-muted mb-1 font-weight-bold">Total Pending</p>
                                            <h4 class="font-weight-bold mb-0 text-info">KES {{ number_format($quoteStats->pending_amount, 0) }}</h4>
                                            <div class="mt-2">
                                                <span class="badge badge-sm bg-gradient-info">{{ $quoteStats->total_quotes > 0 ? round(($quoteStats->pending_quotes / $quoteStats->total_quotes) * 100, 1) : 0 }}%</span>
                                                <span class="text-xs text-muted ms-1">{{ $quoteStats->pending_quotes }} quotes</span>
                                            </div>
                                        </div>
                                        <div class="icon icon-shape bg-gradient-info shadow text-center border-radius-md">
                                            <i class="ni ni-time-alarm text-lg opacity-10" aria-hidden="true"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- RFQ Processor Performance Table -->
                    <h6 class="mb-3">RFQ Processor Performance</h6>
                    <div class="table-responsive p-0">
                        <table class="table align-items-center mb-0">
                            <thead>
                                <tr>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Processor</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Total Quotes</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Total Amount</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Awarded</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Pending Manager</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Pending Customer</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Pending Finance</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Rejected</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Success Rate</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($rfqProcessorStats as $stat)
                                    <tr>
                                        <td>
                                            <div class="d-flex px-3 py-1">
                                                <div class="d-flex flex-column justify-content-center">
                                                    <a href="{{ route('reports.user', ['user' => $rfq_processors->where('name', $stat->name)->first()->id]) }}" class="text-decoration-none">
                                                        <h6 class="mb-0 text-sm text-primary">{{ $stat->name }}</h6>
                                                    </a>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <p class="text-sm font-weight-bold mb-0">{{ $stat->total_quotes }}</p>
                                        </td>
                                        <td>
                                            <p class="text-sm font-weight-bold mb-0">KES {{ number_format($stat->total_amount, 0) }}</p>
                                        </td>
                                        <td>
                                            <p class="text-sm mb-0">
                                                {{ $stat->status_breakdown['completed']['count'] }}
                                                <small class="text-muted">({{ $stat->status_breakdown['completed']['percentage'] }}%)</small><br>
                                                <small class="text-success">KES {{ number_format($stat->status_breakdown['completed']['amount'], 0) }}</small>
                                            </p>
                                        </td>
                                        <td>
                                            <p class="text-sm mb-0">
                                                {{ $stat->status_breakdown['pending_manager']['count'] }}
                                                <small class="text-muted">({{ $stat->status_breakdown['pending_manager']['percentage'] }}%)</small><br>
                                                <small class="text-warning">KES {{ number_format($stat->status_breakdown['pending_manager']['amount'], 0) }}</small>
                                            </p>
                                        </td>
                                        <td>
                                            <p class="text-sm mb-0">
                                                {{ $stat->status_breakdown['pending_customer']['count'] }}
                                                <small class="text-muted">({{ $stat->status_breakdown['pending_customer']['percentage'] }}%)</small><br>
                                                <small class="text-info">KES {{ number_format($stat->status_breakdown['pending_customer']['amount'], 0) }}</small>
                                            </p>
                                        </td>
                                        <td>
                                            <p class="text-sm mb-0">
                                                {{ $stat->status_breakdown['pending_finance']['count'] }}
                                                <small class="text-muted">({{ $stat->status_breakdown['pending_finance']['percentage'] }}%)</small><br>
                                                <small class="text-primary">KES {{ number_format($stat->status_breakdown['pending_finance']['amount'], 0) }}</small>
                                            </p>
                                        </td>
                                        <td>
                                            <p class="text-sm mb-0">
                                                {{ $stat->status_breakdown['rejected']['count'] }}
                                                <small class="text-muted">({{ $stat->status_breakdown['rejected']['percentage'] }}%)</small><br>
                                                <small class="text-danger">KES {{ number_format($stat->status_breakdown['rejected']['amount'], 0) }}</small>
                                            </p>
                                        </td>
                                        <td>
                                            <span class="badge badge-sm bg-gradient-success">{{ number_format($stat->status_breakdown['completed']['percentage'], 1) }}%</span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Export Options -->
    @if(auth()->user()->isRfqApprover() || auth()->user()->isLpoAdmin())
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6 class="mb-0">Export Options</h6>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#exportModal" data-export-type="performance">
                                <i class="fas fa-download me-2"></i>Export Performance Data
                            </button>
                            <button type="button" class="btn btn-info" data-bs-toggle="modal" data-bs-target="#exportModal" data-export-type="analytics">
                                <i class="fas fa-chart-line me-2"></i>Export Analytics
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Quote Analytics -->
    <div class="row">
        <div class="col-lg-8 col-12 mb-4">
            <div class="card h-100">
                <div class="card-header pb-0">
                    <h6>Quote Success Rate Trends</h6>
                </div>
                <div class="card-body">
                    <div class="chart">
                        <canvas id="quote-trends-chart" class="chart-canvas"
                            data-labels="{{ json_encode($quoteTrends->labels) }}"
                            data-values="{{ json_encode($quoteTrends->success_rates) }}">
                        </canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Approval Stats -->
        <div class="col-lg-4 col-12">
            <div class="card h-100">
                <div class="card-header pb-0">
                    <h6>Approval Metrics</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-6">
                            <div class="border-radius-md text-center p-3 bg-gradient-success bg-opacity-10 mb-4">
                                <h6 class="text-sm mb-1">Avg. Approval Time</h6>
                                <h4 class="font-weight-bold mb-0">{{ round($approvalStats->avg_approval_time) }} hours</h4>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="border-radius-md text-center p-3 bg-gradient-info bg-opacity-10 mb-4">
                                <h6 class="text-sm mb-1">Avg. Closing Time</h6>
                                <h4 class="font-weight-bold mb-0">{{ round($approvalStats->avg_closing_time) }} hours</h4>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-12">
                            <h6 class="text-sm mb-3">Approval Rates by Role</h6>
                            <div class="progress-wrapper">
                                <div class="progress-info mb-2">
                                    <div class="progress-percentage">
                                        <span class="text-sm font-weight-bold">RFQ Approver: {{ number_format($approvalStats->approval_rates['manager'], 1) }}%</span>
                                    </div>
                                </div>
                                <div class="progress">
                                    <div class="progress-bar bg-gradient-primary" role="progressbar" aria-valuenow="{{ $approvalStats->approval_rates['manager'] }}" aria-valuemin="0" aria-valuemax="100" style="width: {{ $approvalStats->approval_rates['manager'] }}%;"></div>
                                </div>
                            </div>
                            <div class="progress-wrapper">
                                <div class="progress-info mb-2">
                                    <div class="progress-percentage">
                                        <span class="text-sm font-weight-bold">LPO Admin: {{ number_format($approvalStats->approval_rates['lpo_admin'], 1) }}%</span>
                                    </div>
                                </div>
                                <div class="progress">
                                    <div class="progress-bar bg-gradient-info" role="progressbar" aria-valuenow="{{ $approvalStats->approval_rates['lpo_admin'] }}" aria-valuemin="0" aria-valuemax="100" style="width: {{ $approvalStats->approval_rates['lpo_admin'] }}%;"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Approvals -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header pb-0">
                    <h6>Recent Quote Approvals</h6>
                </div>
                <div class="card-body px-0 pt-0 pb-2">
                    <div class="table-responsive p-0">
                        <table class="table align-items-center mb-0">
                            <thead>
                                <tr>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Quote ID</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Title</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Approved Date</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Approved By</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Closed Date</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($approvalStats->approval_history as $history)
                                    <tr>
                                        <td>
                                            <div class="d-flex px-3 py-1">
                                                <div class="d-flex flex-column justify-content-center">
                                                    <h6 class="mb-0 text-sm">#{{ $history['id'] }}</h6>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <p class="text-sm font-weight-bold mb-0">{{ $history['title'] }}</p>
                                        </td>
                                        <td>
                                            <p class="text-sm font-weight-bold mb-0">{{ $history['approved_at'] }}</p>
                                        </td>
                                        <td>
                                            <p class="text-sm font-weight-bold mb-0">{{ $history['approved_by'] }}</p>
                                        </td>
                                        <td>
                                            <p class="text-sm font-weight-bold mb-0">{{ $history['closed_at'] }}</p>
                                        </td>
                                        <td>
                                            <span class="badge badge-sm bg-gradient-{{
                                                $history['status'] === 'completed' ? 'success' :
                                                ($history['status'] === 'pending_manager' ? 'info' :
                                                ($history['status'] === 'pending_customer' ? 'warning' :
                                                ($history['status'] === 'pending_finance' ? 'primary' : 'danger')))
                                            }}">
                                                {{ ucwords(str_replace('_', ' ', $history['status'])) }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Revenue Forecast Chart -->
    <!-- <div class="row mt-4">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header pb-0">
                    <h6>Revenue Forecast vs Actual</h6>
                </div>
                <div class="card-body">
                    <div class="chart">
                        <canvas id="revenue-forecast-chart" class="chart-canvas"
                            data-labels="{{ json_encode($quoteTrends->labels) }}"
                            data-projected="{{ json_encode($quoteTrends->monthly_approved_amounts) }}"
                            data-actual="{{ json_encode($quoteTrends->monthly_amounts) }}">
                        </canvas>
                    </div>
                </div>
            </div>
        </div>
    </div> -->
</div>

@push('dashboard')
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
<script>
    // Initialize Select2 for quote title search
    $(document).ready(function() {
        $('#quote-title-select').select2({
            theme: 'bootstrap-5',
            placeholder: 'Search quote by title...',
            allowClear: true,
            width: '100%',
            ajax: {
                url: '{{ route("reports.search-quotes") }}',
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return {
                        q: params.term
                    };
                },
                processResults: function (data) {
                    return {
                        results: data.results
                    };
                },
                cache: true
            },
            minimumInputLength: 2
        });
    });

    window.addEventListener('load', function() {
        var ctx = document.getElementById("quote-trends-chart").getContext("2d");
        var chart = ctx.canvas;

        new Chart(ctx, {
            type: "line",
            data: {
                labels: JSON.parse(chart.dataset.labels),
                datasets: [{
                    label: "Success Rate",
                    tension: 0.4,
                    borderWidth: 3,
                    pointRadius: 0,
                    borderColor: "#cb0c9f",
                    backgroundColor: "rgba(203,12,159,0.2)",
                    fill: true,
                    data: JSON.parse(chart.dataset.values),
                    maxBarThickness: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                interaction: {
                    intersect: false,
                    mode: 'index'
                },
                scales: {
                    y: {
                        grid: {
                            drawBorder: false,
                            display: true,
                            drawOnChartArea: true,
                            drawTicks: false,
                            borderDash: [5, 5]
                        },
                        ticks: {
                            display: true,
                            padding: 10,
                            color: '#b2b9bf',
                            font: {
                                size: 11,
                                family: "Open Sans",
                                style: 'normal',
                                lineHeight: 2
                            },
                            callback: function(value) {
                                return value + "%";
                            }
                        }
                    },
                    x: {
                        grid: {
                            drawBorder: false,
                            display: false,
                            drawOnChartArea: false,
                            drawTicks: false,
                            borderDash: [5, 5]
                        },
                        ticks: {
                            display: true,
                            color: '#b2b9bf',
                            padding: 20,
                            font: {
                                size: 11,
                                family: "Open Sans",
                                style: 'normal',
                                lineHeight: 2
                            }
                        }
                    }
                }
            }
        });

        // Revenue Forecast Chart
        var forecastCtx = document.getElementById("revenue-forecast-chart").getContext("2d");
        var forecastChart = forecastCtx.canvas;

        new Chart(forecastCtx, {
            type: "line",
            data: {
                labels: JSON.parse(forecastChart.dataset.labels),
                datasets: [
                    {
                        label: "Projected Revenue",
                        tension: 0.4,
                        borderWidth: 3,
                        pointRadius: 0,
                        borderColor: "#cb0c9f",
                        backgroundColor: "rgba(203,12,159,0.2)",
                        fill: true,
                        data: JSON.parse(forecastChart.dataset.projected)
                    },
                    {
                        label: "Actual Revenue",
                        tension: 0.4,
                        borderWidth: 3,
                        pointRadius: 0,
                        borderColor: "#3498db",
                        backgroundColor: "rgba(52,152,219,0.2)",
                        fill: true,
                        data: JSON.parse(forecastChart.dataset.actual)
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true,
                        position: 'top'
                    }
                },
                interaction: {
                    intersect: false,
                    mode: 'index'
                },
                scales: {
                    y: {
                        grid: {
                            drawBorder: false,
                            display: true,
                            drawOnChartArea: true,
                            drawTicks: false,
                            borderDash: [5, 5]
                        },
                        ticks: {
                            display: true,
                            padding: 10,
                            color: '#b2b9bf',
                            font: {
                                size: 11,
                                family: "Open Sans",
                                style: 'normal',
                                lineHeight: 2
                            },
                            callback: function(value) {
                                return 'KES ' + value.toLocaleString();
                            }
                        }
                    },
                    x: {
                        grid: {
                            drawBorder: false,
                            display: false,
                            drawOnChartArea: false,
                            drawTicks: false
                        },
                        ticks: {
                            display: true,
                            color: '#b2b9bf',
                            padding: 10,
                            font: {
                                size: 11,
                                family: "Open Sans",
                                style: 'normal',
                                lineHeight: 2
                            }
                        }
                    }
                }
            }
        });
    });

    // Animated counter function
    function animateCounter(element, target, duration = 2000, prefix = '', suffix = '') {
        let start = 0;
        const increment = target / (duration / 16); // 60fps
        const timer = setInterval(() => {
            start += increment;
            if (start >= target) {
                clearInterval(timer);
                start = target;
            }
            element.textContent = prefix + Math.floor(start).toLocaleString() + suffix;
        }, 16);
    }



    // Initialize animated counters
    document.addEventListener('DOMContentLoaded', function() {
        // Animate all count-up elements
        document.querySelectorAll('.count-up').forEach(counter => {
            const value = parseFloat(counter.dataset.value);
            if (counter.closest('.numbers').querySelector('.text-sm').textContent.toLowerCase().includes('rate')) {
                animateCounter(counter, value, 1500, '', '%');
            } else {
                animateCounter(counter, value, 1500, '', '');
            }
        });

        // Add hover effects to cards
        document.querySelectorAll('.card').forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-5px)';
                this.style.transition = 'transform 0.3s ease';
            });
            card.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0)';
            });
        });

        // Add row highlight effect
        document.querySelectorAll('tbody tr').forEach(row => {
            row.addEventListener('mouseenter', function() {
                this.style.backgroundColor = 'rgba(0,0,0,0.05)';
                this.style.transition = 'background-color 0.3s ease';
            });
            row.addEventListener('mouseleave', function() {
                this.style.backgroundColor = '';
            });
        });
    });
</script>

<style>
    body {
        overflow-x: hidden;
    }

    .container-fluid {
        padding-left: 15px;
        padding-right: 15px;
    }

    @media (max-width: 768px) {
        .container-fluid {
            padding-left: 10px;
            padding-right: 10px;
        }

        .card-body {
            padding: 1rem 0.75rem;
        }

        .table-responsive {
            font-size: 0.875rem;
        }

        .form-control, .form-select {
            font-size: 14px;
        }
    }

    .card {
        transition: all 0.3s ease;
    }

    .icon-shape {
        transition: all 0.3s ease;
    }

    .card:hover .icon-shape {
        transform: scale(1.1);
    }

    .numbers h5 {
        transition: color 0.3s ease;
    }

    .card:hover .numbers h5 {
        color: #cb0c9f;
    }

    tbody tr {
        cursor: pointer;
    }

    .table-responsive {
        position: relative;
    }

    /* Gradient shadows for table scroll effect */
    .table-responsive::after {
        content: "";
        position: absolute;
        right: 0;
        top: 0;
        bottom: 0;
        width: 30px;
        background: linear-gradient(to right, rgba(255,255,255,0), rgba(255,255,255,1));
        pointer-events: none;
    }

    .table-responsive::before {
        content: "";
        position: absolute;
        left: 0;
        top: 0;
        bottom: 0;
        width: 30px;
        background: linear-gradient(to left, rgba(255,255,255,0), rgba(255,255,255,1));
        pointer-events: none;
        z-index: 1;
    }

    /* Border styles for top 3 RFQ processors */
    .gold-border {
        border-left: 5px solid gold;
    }

    .silver-border {
        border-left: 5px solid silver;
    }

    .bronze-border {
        border-left: 5px solid #CD7F32;
    }
</style>
@endpush

<!-- Export Modal -->
@include('partials.modals.export-modal', ['rfq_processors' => isset($rfq_processors) ? $rfq_processors : [], 'report_type' => 'reports'])

<!-- Correct script for export modal -->
@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const exportModal = document.getElementById('exportModal');
        const typeSelect = document.querySelector('select[name="type"]');

        exportModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const exportType = button.getAttribute('data-export-type');
            if (exportType && typeSelect) {
                typeSelect.value = exportType;
                typeSelect.dispatchEvent(new Event('change'));
            }
        });
    });
</script>
@endpush

@endsection

@extends('layouts.user_type.auth')

@section('content')
<div class="container-fluid py-4">
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

    <!-- KPI Metrics Row -->
    <div class="row mb-4">
        <div class="col-xl-3 col-sm-6">
            <div class="card">
                <div class="card-body p-3">
                    <div class="row">
                        <div class="col-8">
                            <div class="numbers">
                                <p class="text-sm mb-0 text-uppercase font-weight-bold">Monthly Revenue</p>
                                <h5 class="font-weight-bolder mb-0">
                                    KES <span class="count-up" data-value="{{ $financialHealth->projected_monthly_revenue }}">0</span>
                                    <span class="text-{{ $financialHealth->growth_rate >= 0 ? 'success' : 'danger' }} text-sm font-weight-bolder">
                                        {{ ($financialHealth->growth_rate >= 0 ? '+' : '') }}{{ number_format($financialHealth->growth_rate, 1) }}%
                                    </span>
                                </h5>
                            </div>
                        </div>
                        <div class="col-4 text-end">
                            <div class="icon icon-shape bg-gradient-primary shadow text-center border-radius-md">
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
                                <p class="text-sm mb-0 text-uppercase font-weight-bold">Average Deal Size</p>
                                <h5 class="font-weight-bolder mb-0">
                                    KES <span class="count-up" data-value="{{ $quoteStats->avg_value }}">0</span>
                                </h5>
                            </div>
                        </div>
                        <div class="col-4 text-end">
                            <div class="icon icon-shape bg-gradient-success shadow text-center border-radius-md">
                                <i class="ni ni-chart-bar-32 text-lg opacity-10" aria-hidden="true"></i>
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
                                <p class="text-sm mb-0 text-uppercase font-weight-bold">Success Rate</p>
                                <h5 class="font-weight-bolder mb-0">
                                    <span class="count-up" data-value="{{ $quoteStats->success_rate }}">0</span>
                                </h5>
                            </div>
                        </div>
                        <div class="col-4 text-end">
                            <div class="icon icon-shape bg-gradient-info shadow text-center border-radius-md">
                                <i class="ni ni-check-bold text-lg opacity-10" aria-hidden="true"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Export Options -->
    @if(auth()->user()->isRfqApprover())
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

    <!-- RFQ Processor Performance Card -->
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header pb-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6>RFQ Processor Performance Overview</h6>
                        <form method="GET" class="d-flex gap-2">
                            <input type="date" name="date_from" class="form-control form-control-sm" value="{{ request('date_from') }}" placeholder="From">
                            <input type="date" name="date_to" class="form-control form-control-sm" value="{{ request('date_to') }}" placeholder="To">
                            <button type="submit" class="btn btn-primary btn-sm">Filter</button>
                            <a href="{{ route('reports.index') }}" class="btn btn-secondary btn-sm">Reset</a>
                        </form>
                    </div>
                </div>
                <div class="card-body px-0 pt-0 pb-2">
                    <div class="table-responsive p-0">
                        <table class="table align-items-center mb-0">
                            <thead>
                                <tr>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">RFQ Processor</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Quoted vs Awarded</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Status Breakdown</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($rfqProcessorStats as $stat)
                                    <tr class="
                                        @if($loop->first) gold-border
                                        @elseif($loop->iteration == 2) silver-border
                                        @elseif($loop->iteration == 3) bronze-border
                                        @endif
                                    ">
                                        <td>
                                            <div class="d-flex px-3 py-1">
                                                <div class="d-flex flex-column justify-content-center">
                                                    <h6 class="mb-0 text-sm">
                                                        @if($loop->first)
                                                            <i class="fas fa-medal text-warning me-2" title="Gold Medal - Top Performer"></i>&nbsp;
                                                        @elseif($loop->iteration == 2)
                                                            <i class="fas fa-medal text-secondary me-2" title="Silver Medal - Second Place"></i>&nbsp;
                                                        @elseif($loop->iteration == 3)
                                                            <i class="fas fa-medal text-bronze me-2" style="color: #CD7F32;" title="Bronze Medal - Third Place"></i>&nbsp;
                                                        @endif
                                                        {{ $stat->name }}
                                                    </h6>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="mb-2">
                                                <strong>Quoted:</strong> {{ $stat->quoted_vs_awarded['quoted']['count'] }} quotes (KES {{ number_format($stat->quoted_vs_awarded['quoted']['amount'], 0) }})
                                            </div>
                                            <div>
                                                <strong>Awarded:</strong> {{ $stat->quoted_vs_awarded['awarded']['count'] }} quotes ({{ $stat->quoted_vs_awarded['awarded']['percentage'] }}%) - KES {{ number_format($stat->quoted_vs_awarded['awarded']['amount'], 0) }}
                                            </div>
                                        </td>
                                        <td>
                                            <div class="row">
                                                <div class="col-6">
                                                    <small><strong>Pending:</strong> {{ $stat->status_breakdown['pending']['count'] }} ({{ $stat->status_breakdown['pending']['percentage'] }}%)<br>
                                                    KES {{ number_format($stat->status_breakdown['pending']['amount'], 0) }}</small>
                                                </div>
                                                <div class="col-6">
                                                    <small><strong>Approved:</strong> {{ $stat->status_breakdown['approved']['count'] }} ({{ $stat->status_breakdown['approved']['percentage'] }}%)<br>
                                                    KES {{ number_format($stat->status_breakdown['approved']['amount'], 0) }}</small>
                                                </div>
                                            </div>
                                            <div class="row mt-2">
                                                <div class="col-6">
                                                    <small><strong>Completed:</strong> {{ $stat->status_breakdown['completed']['count'] }} ({{ $stat->status_breakdown['completed']['percentage'] }}%)<br>
                                                    KES {{ number_format($stat->status_breakdown['completed']['amount'], 0) }}</small>
                                                </div>
                                                <div class="col-6">
                                                    <small><strong>Rejected:</strong> {{ $stat->status_breakdown['rejected']['count'] }} ({{ $stat->status_breakdown['rejected']['percentage'] }}%)<br>
                                                    KES {{ number_format($stat->status_breakdown['rejected']['amount'], 0) }}</small>
                                                </div>
                                            </div>
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

    <!-- Quote Analytics -->
    <div class="row">
        <div class="col-xl-8">
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
        <div class="col-xl-4">
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
    <div class="row mt-4">
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
    </div>
</div>

@push('dashboard')
<script>
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

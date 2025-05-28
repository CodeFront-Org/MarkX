@extends('layouts.user_type.auth')

@section('content')

  {{-- <div class="row">
    <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
      <div class="card">
        <div class="card-body p-3">
          <div class="row">
            <div class="col-8">
              <div class="numbers">
                <p class="text-sm mb-0 text-capitalize font-weight-bold">Today's Money</p>
                <h5 class="font-weight-bolder mb-0">
                  Kes {{ number_format($todaysMoney, 2) }}
                  <span class="text-{{ $moneyGrowth >= 0 ? 'success' : 'danger' }} text-sm font-weight-bolder">{{ ($moneyGrowth >= 0 ? '+' : '') }}{{ number_format($moneyGrowth, 1) }}%</span>
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
    @if(Auth::user()->role === 'manager')
    <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
      <div class="card">
        <div class="card-body p-3">
          <div class="row">
            <div class="col-8">
              <div class="numbers">
                <p class="text-sm mb-0 text-capitalize font-weight-bold">Today's Users</p>
                <h5 class="font-weight-bolder mb-0">
                  {{ $todaysUsers }}
                  <span class="text-{{ $usersGrowth >= 0 ? 'success' : 'danger' }} text-sm font-weight-bolder">{{ ($usersGrowth >= 0 ? '+' : '') }}{{ number_format($usersGrowth, 1) }}%</span>
                </h5>
              </div>
            </div>
            <div class="col-4 text-end">
              <div class="icon icon-shape bg-gradient-primary shadow text-center border-radius-md">
                <i class="ni ni-world text-lg opacity-10" aria-hidden="true"></i>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    @endif
    <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
      <div class="card">
        <div class="card-body p-3">
          <div class="row">
            <div class="col-8">
              <div class="numbers">
                <p class="text-sm mb-0 text-capitalize font-weight-bold">New Quotes</p>
                <h5 class="font-weight-bolder mb-0">
                  +{{ $newQuotes }}
                  <span class="text-{{ $quotesGrowth >= 0 ? 'success' : 'danger' }} text-sm font-weight-bolder">{{ ($quotesGrowth >= 0 ? '+' : '') }}{{ number_format($quotesGrowth, 1) }}%</span>
                </h5>
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
    <div class="col-xl-3 col-sm-6">
      <div class="card">
        <div class="card-body p-3">
          <div class="row">
            <div class="col-8">
              <div class="numbers">
                <p class="text-sm mb-0 text-capitalize font-weight-bold">Monthly Sales</p>
                <h5 class="font-weight-bolder mb-0">
                  Kes {{ number_format($monthlySales, 2) }}
                  <span class="text-{{ $salesGrowth >= 0 ? 'success' : 'danger' }} text-sm font-weight-bolder">{{ ($salesGrowth >= 0 ? '+' : '') }}{{ number_format($salesGrowth, 1) }}%</span>
                </h5>
              </div>
            </div>
            <div class="col-4 text-end">
              <div class="icon icon-shape bg-gradient-primary shadow text-center border-radius-md">
                <i class="ni ni-cart text-lg opacity-10" aria-hidden="true"></i>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div> --}}

  <div class="row mt-4">
    <div class="col-lg-5 mb-lg-0 mb-4">
      <div class="card z-index-2">
        <div class="card-body p-3">
          <div class="bg-gradient-dark border-radius-lg py-3 pe-1 mb-3">
            <div class="chart">
              <canvas id="chart-bars" class="chart-canvas" height="350"></canvas>
            </div>
          </div>
          <h6 class="ms-2 mt-4 mb-0">Monthly Activity</h6>
          <p class="text-sm ms-2">Quotes vs Success Rate</p>
        </div>
      </div>
    </div>
    <div class="col-lg-7">
      <div class="card z-index-2">
        <div class="card-header pb-0">
          <h6>Marketers Performance</h6>
          <p class="text-sm">
            <i class="fa fa-arrow-up text-success"></i>
            <span class="font-weight-bold">Monthly Performance</span> by Marketer
          </p>
        </div>
        <div class="card-body p-3">
          <div class="chart">
            <canvas id="chart-line" class="chart-canvas" height="350"></canvas>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="row mt-4">
    <div class="col-12">
      <div class="card mb-4">
        <div class="card-header pb-0">
          <h6>Quote Items Per Person Per Day (Last 30 Days)</h6>
          <p class="text-sm">
            <i class="fa fa-table text-success"></i>
            {{-- <span class="font-weight-bold">Daily</span> distribution of quoted items by person (Baseline of 10 items per day) --}}
          </p>
        </div>
        <div class="card-body px-0 pt-0 pb-2">
          <div class="table-responsive p-0">
            <table class="table align-items-center mb-0">
              <thead>
                <tr>
                  <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Person</th>
                  <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Date</th>
                  <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Items Quoted</th>
                  {{-- <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Daily Performance</th> --}}
                </tr>
              </thead>
              <tbody>
                @foreach($quoteItemsByPerson as $item)
                <tr>
                  <td>
                    <div class="d-flex px-2 py-1">
                      <div>
                        <i class="fa fa-user text-primary me-3"></i>
                      </div>
                      <div class="d-flex flex-column justify-content-center">
                        <h6 class="mb-0 text-sm">{{ $item->user_name }}</h6>
                      </div>
                    </div>
                  </td>
                  <td>
                    <p class="text-sm font-weight-bold mb-0">{{ \Carbon\Carbon::parse($item->quote_date)->format('M d, Y') }}</p>
                  </td>
                  <td class="align-middle text-center">
                    <span class="text-secondary text-sm font-weight-bold">{{ $item->item_count }}</span>
                  </td>
                  {{-- <td class="align-middle">
                    <div class="progress-wrapper w-75 mx-auto">
                      <div class="progress-info">
                        <div class="progress-percentage">
                          <span class="text-xs font-weight-bold">{{ min(100, ($item->item_count / 10) * 100) }}%</span>
                        </div>
                      </div>
                      <div class="progress">
                        <div class="progress-bar bg-gradient-info" role="progressbar" 
                             aria-valuenow="{{ min(100, ($item->item_count / 10) * 100) }}" 
                             aria-valuemin="0" 
                             aria-valuemax="100" 
                             style="width: {{ min(100, ($item->item_count / 10) * 100) }}%"></div>
                      </div>
                    </div>
                  </td> --}}
                </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="row my-4">
    <div class="col-lg-8 col-md-6 mb-md-0 mb-4">
      <div class="card">
        <div class="card-header pb-0">
          <div class="row">
            <div class="col-lg-6 col-7">
              <h6>Recent Projects</h6>
              <p class="text-sm mb-0">
                <i class="fa fa-check text-info" aria-hidden="true"></i>
                <span class="font-weight-bold ms-1">{{ $recentProjects->whereIn('status', ['approved', 'completed'])->count() }}</span> completed this month
              </p>
            </div>
          </div>
        </div>
        <div class="card-body px-0 pb-2">
          <div class="table-responsive">
            <table class="table align-items-center mb-0">
              <thead>
                <tr>
                  <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Quote</th>
                  <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Users</th>
                  <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Amount</th>
                  <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Status</th>
                </tr>
              </thead>
              <tbody>
                @foreach($recentProjects as $project)
                <tr>
                  <td>
                    <div class="d-flex px-2 py-1">
                      <div>
                        <i class="fa fa-file-text text-primary me-3"></i>
                      </div>
                      <div class="d-flex flex-column justify-content-center">
                        <h6 class="mb-0 text-sm">{{ $project['title'] }}</h6>
                      </div>
                    </div>
                  </td>
                  <td>
                    <div class="avatar-group mt-2">
                      <span class="text-xs font-weight-bold">{{ $project['user']->name }}</span>
                    </div>
                  </td>
                  <td class="align-middle text-center text-sm">
                    <span class="text-xs font-weight-bold">Kes {{ number_format($project['amount'], 2) }}</span>
                  </td>
                  <td class="align-middle">
                    <div class="progress-wrapper w-75 mx-auto">
                      <div class="progress-info">
                        <div class="progress-percentage">
                          <span class="text-xs font-weight-bold">{{ $project['status'] }}</span>
                        </div>
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
    <div class="col-lg-4 col-md-6">
      <div class="card h-100">
        <div class="card-header pb-0">
          <h6>Recent Activity</h6>
          <p class="text-sm">
            <i class="fa fa-arrow-up text-success" aria-hidden="true"></i>
            <span class="font-weight-bold">{{ collect($recentActivity)->filter(function($item) { return $item['created_at'] >= now()->subMonth(); })->count() }}</span> this month
          </p>
        </div>
        <div class="card-body p-3">
          <div class="timeline timeline-one-side">
            @foreach($recentActivity as $activity)
            <div class="timeline-block mb-3">
              <span class="timeline-step">
                <i class="ni ni-{{ in_array($activity['status'], ['approved', 'completed']) ? 'check-bold' : 'bell-55' }} text-{{ in_array($activity['status'], ['approved', 'completed']) ? 'success' : 'warning' }} text-gradient"></i>
              </span>
              <div class="timeline-content">
                <h6 class="text-dark text-sm font-weight-bold mb-0">
                  {{ $activity['title'] }} - Kes {{ number_format($activity['amount'], 2) }}
                </h6>
                <p class="text-secondary font-weight-bold text-xs mt-1 mb-0">
                  {{ $activity['created_at']->format('d M H:i A') }}
                </p>
              </div>
            </div>
            @endforeach
          </div>
        </div>
      </div>
    </div>
  </div>

@endsection

@push('dashboard')
  <style>
    .chart {
      position: relative;
      height: 350px;
      width: 100%;
    }
    
    .chart-canvas {
      width: 100% !important;
      height: 100% !important;
    }
  </style>
  
  <script>
    document.addEventListener("DOMContentLoaded", function() {
      try {
        console.log('Initializing charts...');
        var ctx = document.getElementById("chart-bars");
        if (!ctx) {
          console.error('Could not find chart-bars canvas element');
          return;
        }
        ctx = ctx.getContext("2d");
        var monthlyData = @json($monthlyData);
        console.log('Monthly data:', monthlyData);

        new Chart(ctx, {
          type: "bar",
          data: {
            labels: monthlyData.map(function(d) { return d.month; }),
            datasets: [{
              label: "Quotes",
              tension: 0.4,
              borderWidth: 0,
              borderRadius: 4,
              borderSkipped: false,
              backgroundColor: "#fff",
              data: monthlyData.map(d => d.quotes),
              maxBarThickness: 6
            }
          ],
          },
          options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
              legend: {
                display: true,
                position: 'top',
                labels: {
                  color: '#fff'
                }
              }
            },
            interaction: {
              intersect: false,
              mode: 'index',
            },
            scales: {
              y: {
                beginAtZero: true, // 👈 important
                suggestedMin: 0,   // 👈 also helps
                grid: {
                  drawBorder: false,
                  display: false,
                  drawOnChartArea: false,
                  drawTicks: false,
                },
                ticks: {
                  suggestedMin: 0,
                  suggestedMax: 500,
                  beginAtZero: true,
                  padding: 15,
                  font: {
                    size: 14,
                    family: "Open Sans",
                    style: 'normal',
                    lineHeight: 2
                  },
                  color: "#fff"
                },
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
                  padding: 15,
                  font: {
                    size: 12,
                    family: "Open Sans",
                    style: 'normal'
                  },
                  color: "#fff"
                },
              },
            },
          },
        });

        var ctx2 = document.getElementById("chart-line");
        if (!ctx2) {
          console.error('Could not find chart-line canvas element');
          return;
        }
        ctx2 = ctx2.getContext("2d");
        
        var marketerData = @json($marketerData);
        // Sort months chronologically to ensure proper order
        var monthsArray = Object.values(marketerData)[0] || [];
        var uniqueMonths = Array.from(new Set(monthsArray.map(function(d) { return d.month; }))).sort(function(a, b) {
          return new Date(a) - new Date(b);
        });
        
        var colors = [
          { line: '#cb0c9f', fill: 'rgba(203,12,159,0.2)' },
          { line: '#3A416F', fill: 'rgba(58,65,111,0.2)' },
          { line: '#17c1e8', fill: 'rgba(23,193,232,0.2)' },
          { line: '#82d616', fill: 'rgba(130,214,22,0.2)' },
          { line: '#ea0606', fill: 'rgba(234,6,6,0.2)' }
        ];

        var datasets = Object.entries(marketerData).map(([name, data], index) => {
          var color = colors[index % colors.length];
          var gradient = ctx2.createLinearGradient(0, 230, 0, 50);
          gradient.addColorStop(1, color.fill);
          gradient.addColorStop(0.2, 'rgba(72,72,176,0.0)');
          gradient.addColorStop(0, color.fill.replace('0.2)', '0)'));

          // Ensure data matches the unique months
          var monthlyAmounts = uniqueMonths.map(month => {
            const monthData = data.find(d => d.month === month);
            return monthData ? monthData.amount : 0;
          });

          return {
            label: name,
            tension: 0.4,
            borderWidth: 0,
            pointRadius: 2,
            pointBackgroundColor: color.line,
            borderColor: color.line,
            borderWidth: 3,
            backgroundColor: gradient,
            fill: true,
            data: monthlyAmounts,
            maxBarThickness: 6
          };
        });

        new Chart(ctx2, {
          type: "line",
          data: {
            labels: uniqueMonths,
            datasets: datasets
          },
          options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
              legend: {
                display: true,
                position: 'top'
              },
              tooltip: {
                callbacks: {
                  label: function(context) {
                    return context.dataset.label + ': KES ' + context.parsed.y.toLocaleString();
                  }
                }
              }
            },
            interaction: {
              intersect: false,
              mode: 'index',
            },
            scales: {
              y: {
                beginAtZero: true, // 👈 important
                suggestedMin: 0,   // 👈 also helps
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
                  },
                  maxRotation: 45,
                  minRotation: 45
                }
              },
            },
          },
        });

        console.log('Charts initialized successfully');
      } catch (error) {
        console.error('Error initializing charts:', error);
      }
    });
  </script>

@endpush


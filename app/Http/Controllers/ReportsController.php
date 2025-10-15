<?php

namespace App\Http\Controllers;

use App\Models\Quote;
use App\Models\QuoteItem;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReportsController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:lpo_admin']);
    }

    public function index(\Illuminate\Http\Request $request)
    {
        // Get RFQ processor performance stats
        $rfqProcessorStats = $this->getRfqProcessorStats($request);
        
        // Get product performance
        [$topProducts, $lowProducts] = $this->getProductPerformance();
        
        // Get quote trends and stats
        $quoteTrends = $this->getQuoteTrends($request);
        $quoteStats = $this->getQuoteStats($request);
        
        // Get approval stats
        $approvalStats = $this->getApprovalStats($request);
        
        // Get quote aging
        $quoteAging = $this->getQuoteAging();
        
        // Get top clients
        $topClients = $this->getTopClients();
        
        // Get financial health metrics
        $financialHealth = $this->getFinancialHealthMetrics();
        
        // Get all RFQ processors for export modal
        $rfq_processors = User::where('role', 'rfq_processor')->get();
        
        return view('reports.index', compact(
            'rfqProcessorStats',
            'topProducts',
            'lowProducts',
            'quoteTrends',
            'quoteStats',
            'approvalStats',
            'quoteAging',
            'topClients',
            'financialHealth',
            'rfq_processors',
            'request'
        ));
    }

    public function searchQuotes(\Illuminate\Http\Request $request)
    {
        $search = $request->get('q', '');
        
        $quotes = Quote::where('title', 'LIKE', "%{$search}%")
            ->select('id', 'title')
            ->limit(20)
            ->get()
            ->map(function($quote) {
                return [
                    'id' => $quote->id,
                    'text' => $quote->title
                ];
            });
        
        return response()->json([
            'results' => $quotes
        ]);
    }

    public function userReport(User $user)
    {
        if ($user->role !== 'rfq_processor') {
            return redirect()->route('reports.index')->with('error', 'User reports are only available for RFQ processors.');
        }

        $quotes = $user->quotes()->with('items')->latest()->paginate(15);
        
        $stats = (object)[
            'total_quotes' => $user->quotes()->count(),
            'total_amount' => $user->quotes()->sum('amount'),
            'completed_quotes' => $user->quotes()->where('status', 'completed')->count(),
            'pending_quotes' => $user->quotes()->whereIn('status', ['pending_manager', 'pending_customer', 'pending_finance'])->count(),
            'rejected_quotes' => $user->quotes()->where('status', 'rejected')->count(),
            'avg_quote_value' => $user->quotes()->avg('amount') ?? 0,
            'success_rate' => $user->quotes()->count() > 0 ? ($user->quotes()->where('status', 'completed')->count() / $user->quotes()->count()) * 100 : 0
        ];

        return view('reports.user', compact('user', 'quotes', 'stats'));
    }

    private function getRfqProcessorStats($request = null)
    {
        $query = User::where('role', 'rfq_processor');
        
        if ($request && $request->filled('user_filter')) {
            $query->where('id', $request->user_filter);
        }
        
        return $query->with(['quotes' => function($query) use ($request) {
                if ($request && $request->filled('date_from')) {
                    $query->whereDate('created_at', '>=', $request->date_from);
                }
                if ($request && $request->filled('date_to')) {
                    $query->whereDate('created_at', '<=', $request->date_to);
                }
                if ($request && $request->filled('quote_title_filter')) {
                    $query->where('id', $request->quote_title_filter);
                }
            }])
            ->get()
            ->map(function($processor) {
                $quotes = $processor->quotes;
                $total_quotes = $quotes->count();
                $total_amount = $quotes->sum('amount');
                
                // Status breakdown
                $pending_manager = $quotes->where('status', 'pending_manager');
                $pending_customer = $quotes->where('status', 'pending_customer');
                $pending_finance = $quotes->where('status', 'pending_finance');
                $completed = $quotes->where('status', 'completed');
                $rejected = $quotes->where('status', 'rejected');
                $awarded = $quotes->where('status', 'completed');
                
                return (object)[
                    'name' => $processor->name,
                    'total_quotes' => $total_quotes,
                    'total_amount' => $total_amount,
                    'quoted_vs_awarded' => [
                        'quoted' => [
                            'count' => $total_quotes,
                            'amount' => $total_amount,
                            'percentage' => 100
                        ],
                        'awarded' => [
                            'count' => $awarded->count(),
                            'amount' => $awarded->sum('amount'),
                            'percentage' => $total_quotes > 0 ? round(($awarded->count() / $total_quotes) * 100, 1) : 0
                        ]
                    ],
                    'status_breakdown' => [
                        'pending_manager' => [
                            'count' => $pending_manager->count(),
                            'amount' => $pending_manager->sum('amount'),
                            'percentage' => $total_quotes > 0 ? round(($pending_manager->count() / $total_quotes) * 100, 1) : 0
                        ],
                        'pending_customer' => [
                            'count' => $pending_customer->count(),
                            'amount' => $pending_customer->sum('amount'),
                            'percentage' => $total_quotes > 0 ? round(($pending_customer->count() / $total_quotes) * 100, 1) : 0
                        ],
                        'pending_finance' => [
                            'count' => $pending_finance->count(),
                            'amount' => $pending_finance->sum('amount'),
                            'percentage' => $total_quotes > 0 ? round(($pending_finance->count() / $total_quotes) * 100, 1) : 0
                        ],
                        'completed' => [
                            'count' => $completed->count(),
                            'amount' => $completed->sum('amount'),
                            'percentage' => $total_quotes > 0 ? round(($completed->count() / $total_quotes) * 100, 1) : 0
                        ],
                        'rejected' => [
                            'count' => $rejected->count(),
                            'amount' => $rejected->sum('amount'),
                            'percentage' => $total_quotes > 0 ? round(($rejected->count() / $total_quotes) * 100, 1) : 0
                        ]
                    ]
                ];
            })
            ->sortByDesc('total_amount')
            ->values();
    }

    private function getProductPerformance()
    {
        $products = QuoteItem::select(
                'item',
                DB::raw('COUNT(*) as total_count'),
                DB::raw('SUM(CASE WHEN quotes.status = "approved" THEN 1 ELSE 0 END) as approved_count'),
                DB::raw('SUM(quantity * price) as total_revenue')
            )
            ->join('quotes', 'quote_items.quote_id', '=', 'quotes.id')
            ->groupBy('item')
            ->having('total_count', '>=', 5) // Minimum threshold for analysis
            ->get()
            ->map(function($product) {
                return (object)[
                    'item' => $product->item,
                    'total_revenue' => $product->total_revenue,
                    'success_rate' => ($product->approved_count / $product->total_count) * 100
                ];
            });

        // Split into top and bottom performers
        $sorted = $products->sortByDesc('total_revenue');
        $topProducts = $sorted->take(5);
        $lowProducts = $sorted->reverse()->take(5);

        return [$topProducts, $lowProducts];
    }

    private function getQuoteTrends($request = null)
    {
        // Add debug logging for SQL query
        try {
            $query = Quote::select(
                DB::raw(DB::connection()->getDriverName() === 'sqlite' 
                    ? "strftime('%Y-%m', created_at) as month" 
                    : "DATE_FORMAT(created_at, '%Y-%m') as month"),
                DB::raw('COUNT(*) as total'),
                DB::raw('SUM(CASE WHEN status = "completed" THEN 1 ELSE 0 END) as approved'),
                DB::raw('SUM(amount) as total_amount'),
                DB::raw('SUM(CASE WHEN status = "completed" THEN amount ELSE 0 END) as approved_amount'),
                DB::raw('AVG(CASE WHEN status = "completed" THEN amount ELSE NULL END) as avg_amount'),
                DB::raw('COUNT(DISTINCT user_id) as unique_users')
            );
            
            // Apply date filters if provided
            if ($request && $request->filled('date_from')) {
                $query->whereDate('created_at', '>=', $request->date_from);
            } else {
                $query->where('created_at', '>=', now()->subMonths(12));
            }
            if ($request && $request->filled('date_to')) {
                $query->whereDate('created_at', '<=', $request->date_to);
            }
            if ($request && $request->filled('user_filter')) {
                $query->where('user_id', $request->user_filter);
            }
            if ($request && $request->filled('quote_title_filter')) {
                $query->where('id', $request->quote_title_filter);
            }
            
            $query->groupBy(DB::connection()->getDriverName() === 'sqlite' 
                ? DB::raw("strftime('%Y-%m', created_at)") 
                : DB::raw("DATE_FORMAT(created_at, '%Y-%m')"));
            
            // Get the raw SQL query for debugging
            $sql = $query->toSql();
            $bindings = $query->getBindings();
            
            // Replace ? with actual values for easier debugging
            foreach ($bindings as $binding) {
                $value = is_numeric($binding) ? $binding : "'".$binding."'";
                $sql = preg_replace('/\?/', $value, $sql, 1);
            }
            
            \Log::info('Quote trends SQL: ' . $sql);
            
            $trends = $query->orderBy('month')->get();
            
            \Log::info('Quote trends count: ' . $trends->count());
            \Log::info('Quote trends data: ' . json_encode($trends));
            
            $currentYear = now()->format('Y');
            
            // If we have fewer than 3 data points, add some synthetic data points
            // to ensure charts display properly
            if ($trends->count() < 3) {
                $currentMonth = now();
                $existingMonths = $trends->pluck('month')->toArray();
                
                // Add previous months if needed
                for ($i = 1; $i <= 3; $i++) {
                    $prevMonth = now()->subMonths($i)->format('Y-m');
                    
                    if (!in_array($prevMonth, $existingMonths)) {
                        $trends->push((object)[
                            'month' => $prevMonth,
                            'total' => 0,
                            'approved' => 0,
                            'total_amount' => 0,
                            'approved_amount' => 0,
                            'avg_amount' => 0,
                            'unique_users' => 0
                        ]);
                    }
                }
                
                // Sort by month to ensure chronological order
                $trends = $trends->sortBy('month')->values();
            }
            
            return (object)[
                'labels' => $trends->pluck('month'),
                'success_rates' => $trends->map(function($trend) {
                    return $trend->total > 0 ? ($trend->approved / $trend->total) * 100 : 0;
                }),
                'monthly_totals' => $trends->pluck('total'),
                'monthly_approved' => $trends->pluck('approved'),
                'monthly_amounts' => $trends->pluck('total_amount'),
                'monthly_approved_amounts' => $trends->pluck('approved_amount'),
                'monthly_avg_amounts' => $trends->pluck('avg_amount'),
                'monthly_users' => $trends->pluck('unique_users'),
                'highest_month' => $trends->max('total'),
                'lowest_month' => $trends->min('total'),
                'best_month' => $trends->sortByDesc('approved_amount')->first(),
                'worst_month' => $trends->sortBy('approved_amount')->first(),
                'average_monthly_quotes' => round($trends->avg('total'), 1),
                'average_monthly_approved' => round($trends->avg('approved'), 1),
                'total_amount_ytd' => $trends->filter(function($trend) use ($currentYear) {
                    return strpos($trend->month, $currentYear) === 0;
                })->sum('approved_amount'),
                'total_quotes_ytd' => $trends->filter(function($trend) use ($currentYear) {
                    return strpos($trend->month, $currentYear) === 0;
                })->sum('total')
            ];
        } catch (\Exception $e) {
            \Log::error('Error in getQuoteTrends: ' . $e->getMessage());
            return (object)[
                'labels' => [],
                'success_rates' => [],
                'monthly_totals' => [],
                'monthly_approved' => [],
                'monthly_amounts' => [],
                'monthly_approved_amounts' => [],
                'monthly_avg_amounts' => [],
                'monthly_users' => [],
                'highest_month' => 0,
                'lowest_month' => 0,
                'best_month' => null,
                'worst_month' => null,
                'average_monthly_quotes' => 0,
                'average_monthly_approved' => 0,
                'total_amount_ytd' => 0,
                'total_quotes_ytd' => 0
            ];
        }
    }

    private function getQuoteStats($request = null)
    {
        $query = Quote::query();
        
        // Apply both date and user filters
        if ($request && $request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request && $request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        if ($request && $request->filled('user_filter')) {
            $query->where('user_id', $request->user_filter);
        }
        if ($request && $request->filled('quote_title_filter')) {
            $query->where('id', $request->quote_title_filter);
        }
        
        $totalQuotes = $query->count();
        $successfulQuotes = (clone $query)->where('status', 'completed')->count();

        // Calculate average time from creation to approval using database-agnostic functions
        $avgTimeToApprove = Quote::whereIn('status', ['approved', 'completed'])
            ->whereNotNull('updated_at')
            ->where('created_at', '>=', now()->subYear())
            ->selectRaw(DB::connection()->getDriverName() === 'sqlite'
                ? 'ROUND(AVG(julianday(updated_at) - julianday(created_at))) AS days'
                : 'ROUND(AVG(TIMESTAMPDIFF(DAY, created_at, updated_at))) AS days')
            ->value('days') ?? 0;

        // Get monthly trend using database-agnostic date functions
        $currentMonth = now()->format('Y-m');
        $lastMonth = now()->subMonth()->format('Y-m');
        
        // Use database-specific date formatting
        if (DB::connection()->getDriverName() === 'sqlite') {
            $monthlyTrend = Quote::whereRaw("strftime('%Y-%m', created_at) = ?", [$currentMonth])
                ->count();

            $lastMonthTrend = Quote::whereRaw("strftime('%Y-%m', created_at) = ?", [$lastMonth])
                ->count();
        } else {
            $monthlyTrend = Quote::whereRaw("DATE_FORMAT(created_at, '%Y-%m') = ?", [$currentMonth])
                ->count();

            $lastMonthTrend = Quote::whereRaw("DATE_FORMAT(created_at, '%Y-%m') = ?", [$lastMonth])
                ->count();
        }

        $trendPercentage = $lastMonthTrend > 0 
            ? (($monthlyTrend - $lastMonthTrend) / $lastMonthTrend) * 100 
            : 0;

        // Calculate amounts by status with date filtering
        $totalQuotedQuery = clone $query;
        $totalQuotedAmount = $totalQuotedQuery->sum('amount') ?? 0;
        
        // Debug logging
        \Log::info('Total Quoted Query SQL: ' . $totalQuotedQuery->toSql());
        \Log::info('Total Quoted Query Bindings: ' . json_encode($totalQuotedQuery->getBindings()));
        \Log::info('Total Quoted Amount: ' . $totalQuotedAmount);
        
        $awardedAmount = (clone $query)->where('status', 'completed')->sum('amount') ?? 0;
        $rejectedAmount = (clone $query)->where('status', 'rejected')->sum('amount') ?? 0;
        $pendingAmount = (clone $query)->whereIn('status', ['pending_manager', 'pending_customer', 'pending_finance'])->sum('amount') ?? 0;

        return (object)[
            'success_rate' => $totalQuotes > 0 ? round(($successfulQuotes / $totalQuotes) * 100, 1) : 0,
            'avg_value' => (clone $query)->where('status', 'completed')->avg('amount') ?? 0,
            'total_value' => $awardedAmount,
            'total_quoted_amount' => $totalQuotedAmount,
            'awarded_amount' => $awardedAmount,
            'rejected_amount' => $rejectedAmount,
            'pending_amount' => $pendingAmount,
            'conversion_time' => round($avgTimeToApprove),
            'trend_percentage' => round($trendPercentage, 1),
            'month_to_date' => $monthlyTrend,
            'last_month' => $lastMonthTrend,
            'total_quotes' => $totalQuotes,
            'successful_quotes' => $successfulQuotes,
            'pending_quotes' => (clone $query)->whereIn('status', ['pending_manager', 'pending_customer', 'pending_finance'])->count(),
            'rejected_quotes' => (clone $query)->where('status', 'rejected')->count(),
            'average_quotes_per_day' => round($totalQuotes / 365, 1),
            'highest_value' => Quote::whereIn('status', ['approved', 'completed'])->max('amount') ?? 0,
            'lowest_value' => Quote::whereIn('status', ['approved', 'completed'])->min('amount') ?? 0
        ];
    }

    private function getApprovalStats($request = null)
    {
        $approvalQuery = Quote::whereNotNull('approved_at')->where('status', '!=', 'rejected');
        $closingQuery = Quote::whereNotNull('closed_at')->where('status', 'completed');
        $historyQuery = Quote::with(['approver', 'closer'])->whereNotNull('approved_at');
        
        // Apply filters
        if ($request && $request->filled('date_from')) {
            $approvalQuery->whereDate('created_at', '>=', $request->date_from);
            $closingQuery->whereDate('created_at', '>=', $request->date_from);
            $historyQuery->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request && $request->filled('date_to')) {
            $approvalQuery->whereDate('created_at', '<=', $request->date_to);
            $closingQuery->whereDate('created_at', '<=', $request->date_to);
            $historyQuery->whereDate('created_at', '<=', $request->date_to);
        }
        if ($request && $request->filled('user_filter')) {
            $approvalQuery->where('user_id', $request->user_filter);
            $closingQuery->where('user_id', $request->user_filter);
            $historyQuery->where('user_id', $request->user_filter);
        }
        if ($request && $request->filled('quote_title_filter')) {
            $approvalQuery->where('id', $request->quote_title_filter);
            $closingQuery->where('id', $request->quote_title_filter);
            $historyQuery->where('id', $request->quote_title_filter);
        }
        
        return (object)[
            'avg_approval_time' => $approvalQuery->selectRaw('AVG(TIMESTAMPDIFF(HOUR, created_at, approved_at)) as avg_hours')
                ->value('avg_hours') ?? 0,
            
            'avg_closing_time' => $closingQuery->selectRaw('AVG(TIMESTAMPDIFF(HOUR, approved_at, closed_at)) as avg_hours')
                ->value('avg_hours') ?? 0,
            
            'approval_rates' => [
                'manager' => $this->calculateRateByRole('manager', $request),
                'lpo_admin' => $this->calculateRateByRole('lpo_admin', $request)
            ],
            
            'approval_history' => $historyQuery->orderBy('approved_at', 'desc')
                ->limit(10)
                ->get()
                ->map(function($quote) {
                    return [
                        'id' => $quote->id,
                        'title' => $quote->title,
                        'approved_at' => $quote->approved_at->format('Y-m-d H:i:s'),
                        'approved_by' => $quote->approver ? $quote->approver->name : 'N/A',
                        'closed_at' => $quote->closed_at ? $quote->closed_at->format('Y-m-d H:i:s') : 'N/A',
                        'closed_by' => $quote->closer ? $quote->closer->name : 'N/A',
                        'status' => $quote->status
                    ];
                })
        ];
    }

    private function calculateRateByRole($role, $request = null)
    {
        $totalQuery = Quote::whereHas('approver', function($query) use ($role) {
            $query->where('role', $role);
        });
        
        $approvedQuery = Quote::whereHas('approver', function($query) use ($role) {
            $query->where('role', $role);
        })->where('status', 'completed');
        
        // Apply filters
        if ($request && $request->filled('date_from')) {
            $totalQuery->whereDate('created_at', '>=', $request->date_from);
            $approvedQuery->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request && $request->filled('date_to')) {
            $totalQuery->whereDate('created_at', '<=', $request->date_to);
            $approvedQuery->whereDate('created_at', '<=', $request->date_to);
        }
        if ($request && $request->filled('user_filter')) {
            $totalQuery->where('user_id', $request->user_filter);
            $approvedQuery->where('user_id', $request->user_filter);
        }
        if ($request && $request->filled('quote_title_filter')) {
            $totalQuery->where('id', $request->quote_title_filter);
            $approvedQuery->where('id', $request->quote_title_filter);
        }
        
        $total = $totalQuery->count();
        $approved = $approvedQuery->count();
        
        return $total > 0 ? ($approved / $total) * 100 : 0;
    }

    private function getQuoteAging()
    {
        $ranges = [
            '0-30 days' => [0, 30],
            '31-60 days' => [31, 60],
            '61-90 days' => [61, 90],
            'Over 90 days' => [91, null]
        ];

        $results = [];
        foreach ($ranges as $label => [$min, $max]) {
            $query = Quote::where('status', 'pending');

            if (DB::connection()->getDriverName() === 'sqlite') {
                if ($max) {
                    $query->whereRaw("ROUND(julianday('now') - julianday(created_at)) BETWEEN ? AND ?", [$min, $max]);
                } else {
                    $query->whereRaw("ROUND(julianday('now') - julianday(created_at)) >= ?", [$min]);
                }
            } else {
                if ($max) {
                    $query->whereRaw("TIMESTAMPDIFF(DAY, created_at, NOW()) BETWEEN ? AND ?", [$min, $max]);
                } else {
                    $query->whereRaw("TIMESTAMPDIFF(DAY, created_at, NOW()) >= ?", [$min]);
                }
            }

            $results[] = (object)[
                'range' => $label,
                'amount' => $query->sum('amount'),
                'count' => $query->count()
            ];
        }

        return collect($results);
    }

    private function getTopClients()
    {
        $daysFunction = DB::connection()->getDriverName() === 'sqlite'
            ? "ROUND(julianday(updated_at) - julianday(created_at))"
            : "TIMESTAMPDIFF(DAY, created_at, updated_at)";
            
        return User::where('role', 'client')
            ->withCount(['quotes as total_quotes'])
            ->withCount(['quotes as approved_quotes' => function($query) {
                $query->where('status', 'approved');
            }])
            ->withSum(['quotes as total_value' => function($query) {
                $query->where('status', 'approved');
            }], 'amount')
            ->withAvg(['quotes as avg_response_days' => function($query) {
                $query->whereNotNull('updated_at')
                    ->whereIn('status', ['approved', 'rejected']);
            }], DB::raw($daysFunction))
            ->orderByDesc('total_value')
            ->limit(10)
            ->get()
            ->map(function($client) {
                // Calculate reliability score based on quote history
                $reliabilityScore = $this->calculateClientReliabilityScore($client);
                
                return (object)[
                    'name' => $client->name,
                    'total_value' => $client->total_value ?? 0,
                    'total_quotes' => $client->total_quotes,
                    'approved_quotes' => $client->approved_quotes,
                    'approval_rate' => $client->total_quotes > 0 
                        ? ($client->approved_quotes / $client->total_quotes) * 100 
                        : 0,
                    'avg_response_time' => round($client->avg_response_days ?? 0),
                    'reliability_score' => $reliabilityScore
                ];
            });
    }

    private function calculateClientReliabilityScore($client)
    {
        $approvalRate = $client->total_quotes > 0 
            ? ($client->approved_quotes / $client->total_quotes) * 100 
            : 0;
        
        $responseTimeScore = $client->avg_response_days 
            ? max(0, 100 - ($client->avg_response_days * 2)) // Deduct points for slower responses
            : 50; // Default score if no data
        
        // Weight the factors (adjust weights as needed)
        $approvalWeight = 0.7;
        $responseTimeWeight = 0.3;
        
        return ($approvalRate * $approvalWeight) + ($responseTimeScore * $responseTimeWeight);
    }

    private function getFinancialHealthMetrics()
    {
        // Get current month and year
        $currentMonth = now()->format('Y-m');
        $lastMonth = now()->subMonth()->format('Y-m');
        $currentYear = now()->format('Y');
        $lastYear = now()->subYear()->format('Y');
        
        // Use database-specific date formatting
        $dateFormat = DB::connection()->getDriverName() === 'sqlite'
            ? "strftime('%Y-%m', created_at)"
            : "DATE_FORMAT(created_at, '%Y-%m')";
            
        $yearFormat = DB::connection()->getDriverName() === 'sqlite'
            ? "strftime('%Y', created_at)"
            : "YEAR(created_at)";
        
        // Calculate current month's projected revenue
        $currentMonthRevenue = Quote::whereIn('status', ['approved', 'completed'])
            ->whereRaw("$dateFormat = ?", [$currentMonth])
            ->sum('amount');

        // Calculate last month's revenue
        $lastMonthRevenue = Quote::whereIn('status', ['approved', 'completed'])
            ->whereRaw("$dateFormat = ?", [$lastMonth])
            ->sum('amount');

        // Calculate growth rate
        $growthRate = $lastMonthRevenue > 0 
            ? (($currentMonthRevenue - $lastMonthRevenue) / $lastMonthRevenue) * 100 
            : 0;

        // Project monthly revenue based on current month's daily average
        $daysInMonth = now()->daysInMonth;
        $daysPassed = now()->day;
        $projected_monthly_revenue = $daysPassed > 0
            ? ($currentMonthRevenue / $daysPassed) * $daysInMonth
            : $currentMonthRevenue;
            
        // Calculate outstanding amount from pending quotes
        $outstanding_amount = Quote::where('status', 'pending')
            ->sum('amount');

        // Calculate conversion rate (approval rate) for current month
        $thisMonthQuotes = Quote::whereRaw("$dateFormat = ?", [$currentMonth])
            ->count();
        
        $thisMonthApproved = Quote::whereRaw("$dateFormat = ?", [$currentMonth])
            ->whereIn('status', ['approved', 'completed'])
            ->count();

        $conversion_rate = $thisMonthQuotes > 0 
            ? ($thisMonthApproved / $thisMonthQuotes) * 100 
            : 0;

        // Get YTD and previous year metrics
        $ytdRevenue = Quote::whereIn('status', ['approved', 'completed'])
            ->whereRaw("$yearFormat = ?", [$currentYear])
            ->sum('amount');
        
        $lastYearRevenue = Quote::whereIn('status', ['approved', 'completed'])
            ->whereRaw("$yearFormat = ?", [$lastYear])
            ->sum('amount');

        $yearOverYearGrowth = $lastYearRevenue > 0 
            ? (($ytdRevenue - $lastYearRevenue) / $lastYearRevenue) * 100 
            : 0;

        // Average daily revenue
        $avgDailyRevenue = $daysPassed > 0
            ? $currentMonthRevenue / $daysPassed
            : 0;

        return (object)[
            'current_monthly_revenue' => $currentMonthRevenue,
            'projected_monthly_revenue' => round($projected_monthly_revenue),
            'growth_rate' => $growthRate,
            'last_month_revenue' => $lastMonthRevenue,
            'outstanding_amount' => $outstanding_amount,
            'conversion_rate' => round($conversion_rate, 1),
            'ytd_revenue' => $ytdRevenue,
            'last_year_revenue' => $lastYearRevenue,
            'year_over_year_growth' => round($yearOverYearGrowth, 1),
            'avg_daily_revenue' => round($avgDailyRevenue),
            'days_in_month' => $daysInMonth,
            'days_passed' => $daysPassed,
            'target_achievement' => $projected_monthly_revenue > 0 
                ? ($currentMonthRevenue / $projected_monthly_revenue) * 100 
                : 0,
            'average_quote_size' => Quote::whereIn('status', ['approved', 'completed'])
                ->whereRaw("$yearFormat = ?", [$currentYear])
                ->avg('amount') ?? 0
        ];
    }
}
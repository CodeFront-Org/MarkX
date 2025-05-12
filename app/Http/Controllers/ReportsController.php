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
        $this->middleware(['auth', 'role:manager']);
    }

    public function index()
    {
        // Get marketer performance stats
        $marketerStats = $this->getMarketerStats();
        
        // Get product performance
        [$topProducts, $lowProducts] = $this->getProductPerformance();
        
        // Get quote trends and stats
        $quoteTrends = $this->getQuoteTrends();
        $quoteStats = $this->getQuoteStats();
        
        // Get approval stats
        $approvalStats = $this->getApprovalStats();
        
        // Get quote aging
        $quoteAging = $this->getQuoteAging();
        
        // Get top clients
        $topClients = $this->getTopClients();
        
        // Get financial health metrics
        $financialHealth = $this->getFinancialHealthMetrics();
        
        return view('reports.index', compact(
            'marketerStats',
            'topProducts',
            'lowProducts',
            'quoteTrends',
            'quoteStats',
            'approvalStats',
            'quoteAging',
            'topClients',
            'financialHealth'
        ));
    }

    private function getMarketerStats()
    {
        return User::where('role', 'marketer')
            ->withCount(['quotes as total_quotes'])
            ->withCount(['quotes as successful_quotes' => function($query) {
                $query->where('status', 'approved');
            }])
            ->withSum(['quotes as quote_value' => function($query) {
                $query->where('status', 'approved');
            }], 'amount')
            ->get()
            ->map(function($marketer) {                return (object)[
                    'name' => $marketer->name,
                    'total_revenue' => $marketer->quote_value ?? 0,
                    'success_rate' => $marketer->total_quotes > 0 
                        ? ($marketer->successful_quotes / $marketer->total_quotes) * 100 
                        : 0,
                    'total_quotes' => $marketer->total_quotes,
                    'approval_rate' => $marketer->total_quotes > 0 
                        ? ($marketer->quotes()->where('status', 'approved')->count() / $marketer->total_quotes) * 100 
                        : 0,
                    'conversion_rate' => $marketer->total_quotes > 0 
                        ? ($marketer->successful_quotes / $marketer->total_quotes) * 100 
                        : 0
                ];
            })
            ->sortByDesc('total_revenue')
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
    }    private function getQuoteTrends()
    {
        $trends = Quote::select(
            DB::raw("strftime('%Y-%m', created_at) as month"),
            DB::raw('COUNT(*) as total'),
            DB::raw('SUM(CASE WHEN status = "approved" THEN 1 ELSE 0 END) as approved'),
            DB::raw('SUM(amount) as total_amount'),
            DB::raw('SUM(CASE WHEN status = "approved" THEN amount ELSE 0 END) as approved_amount'),
            DB::raw('AVG(CASE WHEN status = "approved" THEN amount ELSE NULL END) as avg_amount'),
            DB::raw('COUNT(DISTINCT user_id) as unique_users')
        )
        ->where('created_at', '>=', now()->subMonths(12))
        ->groupBy(DB::raw("strftime('%Y-%m', created_at)"))
        ->orderBy('month')
        ->get();

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
            'total_amount_ytd' => $trends->where('month', '>=', now()->startOfYear()->format('Y-m'))->sum('approved_amount'),
            'total_quotes_ytd' => $trends->where('month', '>=', now()->startOfYear()->format('Y-m'))->sum('total')
        ];
    }    private function getQuoteStats()
    {
        $totalQuotes = Quote::where('created_at', '>=', now()->subYear())->count();
        $successfulQuotes = Quote::where('status', 'approved')
            ->where('created_at', '>=', now()->subYear())
            ->count();

        // Calculate average time from creation to approval
        $avgTimeToApprove = Quote::where('status', 'approved')
            ->whereNotNull('updated_at')
            ->where('created_at', '>=', now()->subYear())
            ->selectRaw('round(avg(julianday(updated_at) - julianday(created_at))) as days')
            ->value('days') ?? 0;

        // Get monthly trend
        $monthlyTrend = Quote::whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->count();

        $lastMonthTrend = Quote::whereYear('created_at', now()->subMonth()->year)
            ->whereMonth('created_at', now()->subMonth()->month)
            ->count();

        $trendPercentage = $lastMonthTrend > 0 
            ? (($monthlyTrend - $lastMonthTrend) / $lastMonthTrend) * 100 
            : 0;

        return (object)[
            'success_rate' => $totalQuotes > 0 ? ($successfulQuotes / $totalQuotes) * 100 : 0,
            'avg_value' => Quote::where('status', 'approved')->avg('amount') ?? 0,
            'total_value' => Quote::where('status', 'approved')->sum('amount') ?? 0,
            'conversion_time' => round($avgTimeToApprove),
            'trend_percentage' => round($trendPercentage, 1),
            'month_to_date' => $monthlyTrend,
            'last_month' => $lastMonthTrend,
            'total_quotes' => $totalQuotes,
            'successful_quotes' => $successfulQuotes,
            'pending_quotes' => Quote::where('status', 'pending')->count(),
            'rejected_quotes' => Quote::where('status', 'rejected')->count(),
            'average_quotes_per_day' => round($totalQuotes / 365, 1),
            'highest_value' => Quote::where('status', 'approved')->max('amount') ?? 0,
            'lowest_value' => Quote::where('status', 'approved')->min('amount') ?? 0
        ];
    }

    private function getApprovalStats()
    {
        $totalQuotes = Quote::where('created_at', '>=', now()->subYear())->count();
        $approvedQuotes = Quote::where('status', 'approved')
            ->where('created_at', '>=', now()->subYear())
            ->count();
        
        $pendingQuotes = Quote::where('status', 'pending')
            ->where('created_at', '>=', now()->subYear())
            ->count();

        return (object)[
            'total_quotes' => $totalQuotes,
            'approved_quotes' => $approvedQuotes,
            'pending_quotes' => $pendingQuotes,
            'approval_rate' => $totalQuotes > 0 ? ($approvedQuotes / $totalQuotes) * 100 : 0
        ];
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

            if ($max) {
                $query->whereRaw("(julianday('now') - julianday(created_at)) BETWEEN ? AND ?", [$min, $max]);
            } else {
                $query->whereRaw("(julianday('now') - julianday(created_at)) >= ?", [$min]);
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
            }], DB::raw('round(julianday(updated_at) - julianday(created_at))'))
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
    }    private function getFinancialHealthMetrics()
    {
        // Calculate current month's projected revenue
        $currentMonthRevenue = Quote::where('status', 'approved')
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('amount');

        // Calculate last month's revenue
        $lastMonthRevenue = Quote::where('status', 'approved')
            ->whereMonth('created_at', now()->subMonth()->month)
            ->whereYear('created_at', now()->subMonth()->year)
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
            : $currentMonthRevenue;        // Calculate outstanding amount from pending quotes
        $outstanding_amount = Quote::where('status', 'pending')
            ->sum('amount');

        // Calculate conversion rate (approval rate) for current month
        $thisMonthQuotes = Quote::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();
        
        $thisMonthApproved = Quote::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->where('status', 'approved')
            ->count();

        $conversion_rate = $thisMonthQuotes > 0 
            ? ($thisMonthApproved / $thisMonthQuotes) * 100 
            : 0;        // Get YTD and previous year metrics
        $ytdRevenue = Quote::where('status', 'approved')
            ->whereYear('created_at', now()->year)
            ->sum('amount');
        
        $lastYearRevenue = Quote::where('status', 'approved')
            ->whereYear('created_at', now()->subYear()->year)
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
            'average_quote_size' => Quote::where('status', 'approved')
                ->whereYear('created_at', now()->year)
                ->avg('amount') ?? 0
        ];
    }
}
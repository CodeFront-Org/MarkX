<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
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
        
        // Get payment analytics
        $paymentStats = $this->getPaymentStats();
        $agingReceivables = $this->getAgingReceivables();
        
        // Get client analytics
        $topClients = $this->getTopClients();
        
        // Get financial projections
        $revenueForecast = $this->getRevenueForecast();
        $financialHealth = $this->getFinancialHealth();

        return view('reports.index', compact(
            'marketerStats',
            'topProducts',
            'lowProducts',
            'quoteTrends',
            'quoteStats',
            'paymentStats',
            'agingReceivables',
            'topClients',
            'revenueForecast',
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
            ->withSum(['quotes as total_revenue' => function($query) {
                $query->whereHas('invoice', function($q) {
                    $q->where('status', 'paid');
                });
            }], 'amount')
            ->get()
            ->map(function($marketer) {
                return (object)[
                    'name' => $marketer->name,
                    'total_revenue' => $marketer->total_revenue ?? 0,
                    'success_rate' => $marketer->total_quotes > 0 
                        ? ($marketer->successful_quotes / $marketer->total_quotes) * 100 
                        : 0,
                    'total_quotes' => $marketer->total_quotes,
                    'conversion_rate' => $marketer->total_quotes > 0 
                        ? ($marketer->quotes()->whereHas('invoice')->count() / $marketer->total_quotes) * 100 
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
                DB::raw('SUM(CASE WHEN approved = 1 THEN 1 ELSE 0 END) as approved_count'),
                DB::raw('SUM(quantity * price) as total_revenue')
            )
            ->whereHas('quote', function($query) {
                $query->whereHas('invoice', function($q) {
                    $q->where('status', 'paid');
                });
            })
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

    private function getQuoteTrends()
    {
        $trends = Quote::select(
            DB::raw("strftime('%Y-%m', created_at) as month"),
            DB::raw('COUNT(*) as total'),
            DB::raw('SUM(CASE WHEN status = "approved" THEN 1 ELSE 0 END) as approved')
        )
        ->where('created_at', '>=', now()->subMonths(12))
        ->groupBy(DB::raw("strftime('%Y-%m', created_at)"))
        ->orderBy('month')
        ->get();

        return (object)[
            'labels' => $trends->pluck('month'),
            'success_rates' => $trends->map(function($trend) {
                return $trend->total > 0 ? ($trend->approved / $trend->total) * 100 : 0;
            })
        ];
    }

    private function getQuoteStats()
    {
        $totalQuotes = Quote::where('created_at', '>=', now()->subYear())->count();
        $successfulQuotes = Quote::where('status', 'approved')
            ->where('created_at', '>=', now()->subYear())
            ->count();

        $avgConversionTime = Quote::where('status', 'converted')
            ->whereHas('invoice')
            ->selectRaw('AVG(round(julianday(updated_at) - julianday(created_at))) as avg_days')
            ->value('avg_days');

        return (object)[
            'success_rate' => $totalQuotes > 0 ? ($successfulQuotes / $totalQuotes) * 100 : 0,
            'avg_value' => Quote::where('status', 'approved')->avg('amount') ?? 0,
            'conversion_time' => round($avgConversionTime ?? 0)
        ];
    }

    private function getPaymentStats()
    {
        $paidInvoices = Invoice::where('status', 'paid')
            ->whereNotNull('paid_at')
            ->where('paid_at', '>=', now()->subYear());

        $avgDaysToPayment = $paidInvoices
            ->selectRaw('AVG(round(julianday(paid_at) - julianday(created_at))) as avg_days')
            ->value('avg_days');

        $totalInvoices = Invoice::where('status', '!=', 'draft')
            ->where('created_at', '>=', now()->subYear())
            ->count();
        
        $overdueInvoices = Invoice::where('status', 'overdue')
            ->where('created_at', '>=', now()->subYear())
            ->count();

        return (object)[
            'avg_days_to_payment' => round($avgDaysToPayment ?? 0),
            'overdue_rate' => $totalInvoices > 0 ? ($overdueInvoices / $totalInvoices) * 100 : 0
        ];
    }

    private function getAgingReceivables()
    {
        $ranges = [
            '0-30 days' => [0, 30],
            '31-60 days' => [31, 60],
            '61-90 days' => [61, 90],
            'Over 90 days' => [91, null]
        ];

        $results = [];
        foreach ($ranges as $label => [$min, $max]) {
            $query = Invoice::where('status', 'final')
                ->whereNull('paid_at');

            if ($max) {
                $query->whereRaw("julianday('now') - julianday(created_at) BETWEEN ? AND ?", [$min, $max]);
            } else {
                $query->whereRaw("julianday('now') - julianday(created_at) >= ?", [$min]);
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
        return User::where('role', 'marketer')
            ->withCount(['invoices as paid_invoices_count' => function($query) {
                $query->where('status', 'paid');
            }])
            ->withSum(['invoices as total_revenue' => function($query) {
                $query->where('status', 'paid');
            }], 'amount')
            ->withAvg(['invoices as avg_payment_days' => function($query) {
                $query->whereNotNull('paid_at')
                    ->where('status', 'paid');
            }], DB::raw('round(julianday(paid_at) - julianday(created_at))'))
            ->orderByDesc('total_revenue')
            ->limit(10)
            ->get()
            ->map(function($client) {
                // Calculate reliability score based on payment history
                $reliabilityScore = $this->calculateReliabilityScore($client);
                
                return (object)[
                    'name' => $client->name,
                    'total_revenue' => $client->total_revenue ?? 0,
                    'paid_invoices_count' => $client->paid_invoices_count,
                    'avg_payment_days' => round($client->avg_payment_days ?? 0),
                    'reliability_score' => $reliabilityScore
                ];
            });
    }

    private function calculateReliabilityScore($client)
    {
        $factors = [
            // Payment timeliness (40% weight)
            'timeliness' => $client->avg_payment_days <= 30 ? 4 : 
                          ($client->avg_payment_days <= 45 ? 3 : 
                          ($client->avg_payment_days <= 60 ? 2 : 1)),
            
            // Payment consistency (30% weight)
            'consistency' => $client->paid_invoices_count >= 10 ? 3 : 
                          ($client->paid_invoices_count >= 5 ? 2 : 1),
            
            // Revenue contribution (30% weight)
            'revenue' => $client->total_revenue >= 1000000 ? 3 : 
                       ($client->total_revenue >= 500000 ? 2 : 1)
        ];

        return (
            ($factors['timeliness'] * 4) + 
            ($factors['consistency'] * 3) + 
            ($factors['revenue'] * 3)
        ) / 1.0;
    }

    private function getRevenueForecast()
    {
        $historicalData = Invoice::where('status', 'paid')
            ->where('paid_at', '>=', now()->subMonths(12))
            ->selectRaw("strftime('%Y-%m', paid_at) as month, SUM(amount) as total")
            ->groupBy(DB::raw("strftime('%Y-%m', paid_at)"))
            ->orderBy('month')
            ->get();

        // Calculate trend for projection
        $trend = $this->calculateRevenueTrend($historicalData);
        
        // Project next 6 months
        $projectedData = collect();
        $lastMonth = Carbon::now();
        
        for ($i = 1; $i <= 6; $i++) {
            $lastMonth = $lastMonth->copy()->addMonth();
            $projectedAmount = $trend['base'] + ($trend['growth'] * $i);
            $projectedData->push([
                'month' => $lastMonth->format('Y-m'),
                'amount' => max(0, $projectedAmount) // Ensure no negative projections
            ]);
        }

        return (object)[
            'labels' => $historicalData->pluck('month')->merge($projectedData->pluck('month')),
            'actual_values' => $historicalData->pluck('total'),
            'projected_values' => $historicalData->pluck('total')->merge($projectedData->pluck('amount'))
        ];
    }

    private function calculateRevenueTrend($historicalData)
    {
        if ($historicalData->count() < 2) {
            return ['base' => 0, 'growth' => 0];
        }

        $x = range(0, $historicalData->count() - 1);
        $y = $historicalData->pluck('total')->toArray();

        $n = count($x);
        $sumX = array_sum($x);
        $sumY = array_sum($y);
        $sumXY = array_sum(array_map(function($x, $y) { return $x * $y; }, $x, $y));
        $sumX2 = array_sum(array_map(function($x) { return $x * $x; }, $x));

        $slope = ($n * $sumXY - $sumX * $sumY) / ($n * $sumX2 - $sumX * $sumX);
        $intercept = ($sumY - $slope * $sumX) / $n;

        return [
            'base' => end($y),
            'growth' => $slope
        ];
    }

    private function getFinancialHealth()
    {
        // Calculate projected monthly revenue
        $lastMonthRevenue = Invoice::where('status', 'paid')
            ->whereMonth('paid_at', now()->subMonth())
            ->sum('amount');
        
        $thisMonthRevenue = Invoice::where('status', 'paid')
            ->whereMonth('paid_at', now())
            ->sum('amount');

        // Calculate growth rate
        $growthRate = $lastMonthRevenue > 0 
            ? (($thisMonthRevenue - $lastMonthRevenue) / $lastMonthRevenue) * 100
            : 0;

        // Get outstanding amount - include all unpaid invoices that are:
        // 1. Final or overdue status AND not paid
        // 2. Past their due date
        $outstandingAmount = Invoice::where(function($query) {
                $query->whereIn('status', ['final', 'overdue'])
                    ->whereNull('paid_at');
            })
            ->where(function($query) {
                $query->whereDate('due_date', '<', now())
                    ->orWhere('status', 'overdue');
            })
            ->sum('amount');

        return (object)[
            'projected_monthly_revenue' => $thisMonthRevenue * (1 + ($growthRate / 100)),
            'growth_rate' => $growthRate,
            'outstanding_amount' => $outstandingAmount
        ];
    }
}
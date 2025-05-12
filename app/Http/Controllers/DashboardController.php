<?php

namespace App\Http\Controllers;

use App\Models\Quote;
use App\Models\User;
use App\Models\QuoteItem;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {        
        // Base query for scoping data
        $quotesQuery = Auth::user()->role === 'manager' 
            ? Quote::query() 
            : Quote::where('user_id', Auth::id());
        
        // Today's Money - Sum of approved quotes today
        $todaysMoney = (clone $quotesQuery)
            ->where('status', 'approved')
            ->whereDate('created_at', today())
            ->sum('amount');
        
        $yesterdayMoney = (clone $quotesQuery)
            ->where('status', 'approved')
            ->whereDate('created_at', today()->subDay())
            ->sum('amount');
        $moneyGrowth = $yesterdayMoney > 0 ? 
            (($todaysMoney - $yesterdayMoney) / $yesterdayMoney) * 100 : 0;

        // Today's Users - For non-managers, this will always be 1 or 0
        if (Auth::user()->role === 'manager') {
            $todaysUsers = User::whereHas('quotes', function($q) {
                $q->whereDate('created_at', today());
            })->count();

            $yesterdayUsers = User::whereHas('quotes', function($q) {
                $q->whereDate('created_at', today()->subDay());
            })->count();
        } else {
            $todaysUsers = (clone $quotesQuery)
                ->whereDate('created_at', today())
                ->exists() ? 1 : 0;
            
            $yesterdayUsers = (clone $quotesQuery)
                ->whereDate('created_at', today()->subDay())
                ->exists() ? 1 : 0;
        }
        $usersGrowth = $yesterdayUsers > 0 ? 
            (($todaysUsers - $yesterdayUsers) / $yesterdayUsers) * 100 : 0;

        // New Quotes - Count of new quotes created today
        $newQuotes = (clone $quotesQuery)
            ->whereDate('created_at', today())
            ->count();
        $yesterdayQuotes = (clone $quotesQuery)
            ->whereDate('created_at', today()->subDay())
            ->count();
        $quotesGrowth = $yesterdayQuotes > 0 ? 
            (($newQuotes - $yesterdayQuotes) / $yesterdayQuotes) * 100 : 0;

        // Monthly Sales - Sum of all approved quotes this month
        $monthlySales = (clone $quotesQuery)
            ->where('status', 'approved')
            ->whereMonth('created_at', now()->month)
            ->sum('amount');
        $lastMonthSales = (clone $quotesQuery)
            ->where('status', 'approved')
            ->whereMonth('created_at', now()->subMonth()->month)
            ->sum('amount');
        $salesGrowth = $lastMonthSales > 0 ? 
            (($monthlySales - $lastMonthSales) / $lastMonthSales) * 100 : 0;

        // Monthly activity data for charts
        $monthlyData = [];
        $startDate = now()->startOfYear(); // Start from January of current year
        
        for ($date = $startDate->copy(); $date <= now(); $date->addMonth()) {
            $monthlyData[] = [
                'month' => $date->format('M Y'),
                'quotes' => (clone $quotesQuery)
                    ->whereYear('created_at', $date->year)
                    ->whereMonth('created_at', $date->month)
                    ->count(),
                'approved_quotes' => (clone $quotesQuery)
                    ->whereYear('created_at', $date->year)
                    ->whereMonth('created_at', $date->month)
                    ->where('status', 'approved')
                    ->count()
            ];
        }
        $monthlyData = collect($monthlyData);

        // Recent projects
        $recentProjects = (clone $quotesQuery)
            ->with(['user'])
            ->latest()
            ->take(6)
            ->get()
            ->map(function($quote) {
                $completion = match($quote->status) {
                    'approved' => 100,
                    'rejected' => 100,
                    'pending' => 50,
                    default => 10
                };
                return [
                    'title' => $quote->title,
                    'amount' => $quote->amount,
                    'status' => $quote->status,
                    'completion' => $completion,
                    'user' => $quote->user
                ];
            });

        // Recent activity
        $recentActivity = (clone $quotesQuery)
            ->with(['user'])  // Include user relationship
            ->where('status', '!=', 'draft')
            ->latest()
            ->take(6)
            ->get()
            ->map(function($quote) {
                return [
                    'title' => $quote->title,
                    'amount' => $quote->amount,
                    'status' => $quote->status,
                    'created_at' => $quote->created_at,
                    'user' => $quote->user
                ];
            });

        // Get marketers data for the line chart
        if (Auth::user()->role === 'manager') {
            $marketers = User::where('role', 'marketer')->get();
        } else {
            $marketers = User::where('id', Auth::id())->get();
        }
        
        $marketerData = [];
        foreach ($marketers as $marketer) {
            $monthlyPerformance = [];
            $date = now()->startOfYear(); // Start from January of current year
            
            while ($date <= now()) {
                $amount = Quote::where('user_id', $marketer->id)
                    ->where('status', 'approved')
                    ->whereYear('created_at', $date->year)
                    ->whereMonth('created_at', $date->month)
                    ->sum('amount');

                $monthlyPerformance[] = [
                    'month' => $date->format('M Y'),
                    'amount' => $amount
                ];
                
                $date->addMonth();
            }
            $marketerData[$marketer->name] = $monthlyPerformance;
        }

        // Quote items per person per day
        $quoteItemsByPerson = $this->getQuoteItemsByPersonPerDay();

        return view('dashboard', compact(
            'todaysMoney',
            'moneyGrowth',
            'todaysUsers',
            'usersGrowth',
            'newQuotes',
            'quotesGrowth',
            'monthlySales',
            'salesGrowth',
            'monthlyData',
            'recentProjects',
            'recentActivity',
            'marketerData',
            'quoteItemsByPerson'
        ));
    }    private function getQuoteItemsByPersonPerDay()
    {
        $query = QuoteItem::select([
            'users.name as user_name',
            DB::raw('DATE(quote_items.created_at) as quote_date'),
            DB::raw('COUNT(*) as item_count')
        ])
        ->join('quotes', 'quotes.id', '=', 'quote_items.quote_id')
        ->join('users', 'users.id', '=', 'quotes.user_id')
        ->where('quotes.created_at', '>=', now()->subDays(30));

        // If user is not a manager, only show their own data
        if (Auth::user()->role !== 'manager') {
            $query->where('quotes.user_id', Auth::id());
        }

        return $query->groupBy('users.name', DB::raw('DATE(quote_items.created_at)'))
            ->orderBy('quote_date', 'desc')
            ->orderBy('users.name', 'asc')
            ->get();
    }
}
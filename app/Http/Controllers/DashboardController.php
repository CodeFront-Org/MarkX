<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Quote;
use App\Models\User;
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
            
        $invoicesQuery = Auth::user()->role === 'manager'
            ? Invoice::query()
            : Invoice::whereHas('quote', function($query) {
                $query->where('user_id', Auth::id());
            });

        // Today's Money - Sum of paid invoices today
        $todaysMoney = clone $invoicesQuery;
        $todaysMoney = $todaysMoney->where('status', 'paid')
            ->whereDate('paid_at', today())
            ->sum('amount');
        
        $yesterdayMoney = clone $invoicesQuery;
        $yesterdayMoney = $yesterdayMoney->where('status', 'paid')
            ->whereDate('paid_at', today()->subDay())
            ->sum('amount');
        $moneyGrowth = $yesterdayMoney > 0 ? 
            (($todaysMoney - $yesterdayMoney) / $yesterdayMoney) * 100 : 0;

        // Today's Users - For non-managers, this will always be 1 or 0
        if (Auth::user()->role === 'manager') {
            $todaysUsers = User::whereHas('quotes', function($q) {
                $q->whereDate('created_at', today());
            })->orWhereHas('invoices', function($q) {
                $q->whereDate('created_at', today());
            })->count();

            $yesterdayUsers = User::whereHas('quotes', function($q) {
                $q->whereDate('created_at', today()->subDay());
            })->orWhereHas('invoices', function($q) {
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

        // Monthly Sales - Sum of all paid invoices this month
        $monthlySales = (clone $invoicesQuery)
            ->where('status', 'paid')
            ->whereMonth('paid_at', now()->month)
            ->sum('amount');
        $lastMonthSales = (clone $invoicesQuery)
            ->where('status', 'paid')
            ->whereMonth('paid_at', now()->subMonth()->month)
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
                'invoices' => (clone $invoicesQuery)
                    ->whereYear('created_at', $date->year)
                    ->whereMonth('created_at', $date->month)
                    ->where('status', 'paid')
                    ->count()
            ];
        }
        $monthlyData = collect($monthlyData);

        // Recent projects
        $recentProjects = (clone $quotesQuery)
            ->with(['user', 'invoice'])
            ->latest()
            ->take(6)
            ->get()
            ->map(function($quote) {
                $completion = match($quote->status) {
                    'approved' => 60,
                    'converted' => 100,
                    'rejected' => 100,
                    default => 10
                };
                return [
                    'title' => $quote->title,
                    'amount' => $quote->amount,
                    'status' => $quote->status,
                    'completion' => $completion,
                    'user' => $quote->user,
                    'invoice' => $quote->invoice
                ];
            });

        // Recent activity
        $recentActivity = (clone $invoicesQuery)
            ->with('quote')
            ->where('status', '!=', 'draft')
            ->latest()
            ->take(6)
            ->get();

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
                // Include both approved quotes and converted quotes (which become invoices)
                $amount = Invoice::whereHas('quote', function($query) use ($marketer) {
                    $query->where('user_id', $marketer->id);
                })
                ->whereYear('created_at', $date->year)
                ->whereMonth('created_at', $date->month)
                ->where('status', 'paid')
                ->sum('amount');

                $monthlyPerformance[] = [
                    'month' => $date->format('M Y'),
                    'amount' => $amount
                ];
                
                $date->addMonth();
            }
            $marketerData[$marketer->name] = $monthlyPerformance;
        }

        return view('dashboard', compact(
            'todaysMoney', 'moneyGrowth',
            'todaysUsers', 'usersGrowth',
            'newQuotes', 'quotesGrowth',
            'monthlySales', 'salesGrowth',
            'monthlyData',
            'recentProjects',
            'recentActivity',
            'marketerData'
        ));
    }
}
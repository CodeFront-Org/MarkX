<?php

namespace App\Http\Controllers;

use App\Models\Quote;
use App\Models\QuoteItem;
use App\Models\User;
use App\Models\ProductItem;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\DataExport;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use Barryvdh\DomPDF\Facade\Pdf;
use Symfony\Component\HttpFoundation\Response;

class ExportController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:rfq_approver']);
    }

    public function exportData(Request $request)
    {
        try {
            // Validate request
            $request->validate([
                'type' => 'required|in:quotes,rfq_processors,products,performance,analytics,items',
                'format' => 'required|in:excel,csv,pdf',
                'dateFrom' => 'nullable|date',
                'dateTo' => 'nullable|date|after_or_equal:dateFrom',
                'rfq_processor' => 'nullable|exists:users,id',
                'status' => 'nullable|string',
            ]);

            // Build query based on export type
            $data = $this->buildQuery($request);

            if ($data->isEmpty()) {
                return back()->with('error', 'No data available for export with the selected filters. Please try different filters.');
            }

            // Format data based on export type
            $formattedData = $this->formatData($data, $request->type);

            // Generate response in requested format
            $response = $this->generateResponse($formattedData, $request->format, $request->type);

            // For file downloads, we need to handle the success message differently
            // Store success message in session and return the download response
            session()->flash('export_success', ucfirst($request->type) . ' data exported successfully as ' . strtoupper($request->format));
            
            // Add a header to trigger page refresh after download
            if ($response instanceof \Symfony\Component\HttpFoundation\BinaryFileResponse) {
                $response->headers->set('X-Export-Success', 'true');
            }

            return $response;
        } catch (\Exception $e) {
            \Log::error('Export error: ' . $e->getMessage());
            return back()->with('error', 'Error exporting data: ' . $e->getMessage());
        }
    }

    private function buildQuery(Request $request)
    {
        $query = null;
        $dateFrom = $request->dateFrom ? Carbon::parse($request->dateFrom) : null;
        $dateTo = $request->dateTo ? Carbon::parse($request->dateTo) : null;

        switch ($request->type) {
            case 'quotes':
                $query = Quote::query()
                    ->with(['user', 'items'])
                    ->when($dateFrom, fn($q) => $q->where(function($subQ) use ($dateFrom) {
                        $subQ->where('status', 'completed')
                            ->whereNotNull('closed_at')
                            ->whereDate('closed_at', '>=', $dateFrom);
                    }))
                    ->when($dateTo, fn($q) => $q->where(function($subQ) use ($dateTo) {
                        $subQ->where('status', 'completed')
                            ->whereNotNull('closed_at')
                            ->whereDate('closed_at', '<=', $dateTo);
                    }))
                    ->when($request->rfq_processor, fn($q) => $q->where('user_id', $request->rfq_processor))
                    ->when($request->status, fn($q) => $q->where('status', $request->status));
                break;

            case 'rfq_processors':
                $query = User::where('role', 'rfq_processor')
                    ->withCount(['quotes as quotes_count' => function($q) use ($dateFrom, $dateTo) {
                        $q->when($dateFrom, fn($q) => $q->whereDate('created_at', '>=', $dateFrom))
                          ->when($dateTo, fn($q) => $q->whereDate('created_at', '<=', $dateTo));
                    }])
                    ->withSum(['quotes as quotes_sum_total_amount' => function($q) use ($dateFrom, $dateTo) {
                        $q->when($dateFrom, fn($q) => $q->whereDate('created_at', '>=', $dateFrom))
                          ->when($dateTo, fn($q) => $q->whereDate('created_at', '<=', $dateTo));
                    }], 'amount');
                break;

            case 'products':
                $query = ProductItem::query()
                    ->withCount(['quotes' => function($q) use ($dateFrom, $dateTo) {
                        $q->when($dateFrom, fn($q) => $q->whereDate('created_at', '>=', $dateFrom))
                          ->when($dateTo, fn($q) => $q->whereDate('created_at', '<=', $dateTo));
                    }]);
                break;

            case 'items':
                $query = QuoteItem::select(
                        'quote_items.item',
                        DB::raw('COUNT(*) as total_count'),
                        DB::raw('SUM(quantity) as total_quantity'),
                        DB::raw('AVG(price) as average_price'),
                        DB::raw('SUM(quantity * price) as total_value')
                    )
                    ->join('quotes', 'quote_items.quote_id', '=', 'quotes.id')
                    ->when($dateFrom, fn($q) => $q->whereDate('quotes.created_at', '>=', $dateFrom))
                    ->when($dateTo, fn($q) => $q->whereDate('quotes.created_at', '<=', $dateTo))
                    ->when($request->rfq_processor, fn($q) => $q->where('quotes.user_id', $request->rfq_processor))
                    ->when($request->status, fn($q) => $q->where('quotes.status', $request->status))
                    ->groupBy('quote_items.item')
                    ->orderByDesc('total_value');
                break;

            case 'performance':
                $query = DB::table('quotes')
                    ->join('users', 'quotes.user_id', '=', 'users.id')
                    ->leftJoin('quote_items', 'quotes.id', '=', 'quote_items.quote_id')
                    ->select(
                        'users.name as rfq_processor_name',
                        DB::raw('COUNT(DISTINCT quotes.id) as total_quotes'),
                        DB::raw('SUM(quotes.amount) as total_amount'),
                        DB::raw('AVG(quotes.amount) as average_quote_value'),
                        DB::raw('COUNT(DISTINCT quote_items.item) as unique_products_sold')
                    )
                    ->when($dateFrom, fn($q) => $q->whereDate('quotes.created_at', '>=', $dateFrom))
                    ->when($dateTo, fn($q) => $q->whereDate('quotes.created_at', '<=', $dateTo))
                    ->when($request->rfq_processor, fn($q) => $q->where('quotes.user_id', $request->rfq_processor))
                    ->groupBy('users.id', 'users.name');
                break;

            case 'analytics':
                $query = Quote::selectRaw('
                    DATE(created_at) as date,
                    COUNT(*) as total_quotes,
                    SUM(amount) as total_revenue,
                    AVG(amount) as average_quote_value,
                    COUNT(DISTINCT user_id) as active_rfq_processors
                ')
                ->when($dateFrom, fn($q) => $q->whereDate('created_at', '>=', $dateFrom))
                ->when($dateTo, fn($q) => $q->whereDate('created_at', '<=', $dateTo))
                ->when($request->rfq_processor, fn($q) => $q->where('user_id', $request->rfq_processor))
                ->groupBy(DB::raw('DATE(created_at)'))
                ->orderBy('date');
                break;
        }

        return $query ? $query->get() : collect([]);
    }

    private function formatData($data, $type)
    {
        if ($data->isEmpty()) {
            return [];
        }
        
        $data = collect($data);
        
        switch ($type) {
            case 'quotes':
                return $data->map(function($quote) {
                    // Calculate approved items amount
                    $approvedAmount = $quote->items ? $quote->items->where('approved', true)->sum(function($item) {
                        return $item->quantity * $item->price;
                    }) : 0;
                    
                    return [
                        'Quote Title' => $quote->title ?? 'N/A',
                        'Date' => $quote->closed_at ? $quote->closed_at->format('Y-m-d') : $quote->created_at->format('Y-m-d'),
                        'RFQ Processor' => $quote->user ? $quote->user->name : 'N/A',
                        'Status' => $this->formatStatusForDisplay($quote->status),
                        'Amount' => $approvedAmount,
                        'Items Count' => $quote->items ? $quote->items->where('approved', true)->count() : 0,
                    ];
                });

            case 'rfq_processors':
                return $data->map(function($user) {
                    return [
                        'ID' => $user->id,
                        'Name' => $user->name,
                        'Email' => $user->email,
                        'Total Quotes' => $user->quotes_count ?? 0,
                        'Total Revenue' => $user->quotes_sum_total_amount ?? 0,
                        'Average Quote Value' => ($user->quotes_count && $user->quotes_sum_total_amount) ? 
                            $user->quotes_sum_total_amount / $user->quotes_count : 0,
                    ];
                });

            case 'products':
                return $data->map(function($item) {
                    return [
                        'ID' => $item->id,
                        'Name' => $item->name,
                        'Description' => $item->description ?? 'N/A',
                        'Price' => $item->price ?? 0,
                        'Times Quoted' => $item->quotes_count ?? 0,
                    ];
                });

            // Performance and analytics data is already formatted by the query
            default:
                return $data->map(function($item) {
                    return (array) $item;
                });
        }
    }

    private function generateResponse($data, $format, $type)
    {
        $filename = sprintf('%s_export_%s', $type, now()->format('Y-m-d'));

        try {
            // Check if data is empty
            if (empty($data)) {
                return back()->with('error', 'No data available to export with the selected filters.');
            }
            
            switch ($format) {
                case 'excel':
                    return Excel::download(new DataExport($data), $filename . '.xlsx');

                case 'csv':
                    return response($this->arrayToCsv($data))
                        ->header('Content-Type', 'text/csv')
                        ->header('Content-Disposition', "attachment; filename=\"$filename.csv\"");
                        
                case 'pdf':
                    $headers = !empty($data) ? array_keys((array)$data[0]) : [];
                    
                    // Format data for better PDF display
                    $formattedData = $this->formatDataForPdf($data, $type);
                    
                    // Determine page orientation based on number of columns
                    $orientation = count($headers) > 5 ? 'landscape' : 'portrait';
                    
                    // Use specialized templates based on export type
                    $view = match($type) {
                        'performance' => 'exports.performance-report',
                        'quotes' => 'exports.quotes-report',
                        'analytics' => 'exports.analytics-report',
                        'products' => 'exports.products-report',
                        'items' => 'exports.items-report',
                        default => 'exports.pdf'
                    };
                    
                    $pdf = Pdf::loadView($view, [
                        'type' => $type,
                        'headers' => $headers,
                        'data' => $formattedData,
                        'filters' => array_filter([
                            'date range' => request('dateFrom') && request('dateTo') ? 
                                request('dateFrom') . ' to ' . request('dateTo') : null,
                            'rfq_processor' => request('rfq_processor') ? User::find(request('rfq_processor'))->name : null,
                            'status' => request('status'),
                        ])
                    ]);
                    
                    // Set PDF options
                    $pdf->setPaper('a4', $orientation);
                    $pdf->setOptions([
                        'dpi' => 150,
                        'defaultFont' => 'sans-serif',
                        'isHtml5ParserEnabled' => true,
                        'isRemoteEnabled' => true
                    ]);
                    
                    return $pdf->download($filename . '.pdf');

                default:
                    return response()->json($data);
            }
        } catch (\Exception $e) {
            \Log::error('Export error: ' . $e->getMessage());
            return back()->with('error', 'Error generating export: ' . $e->getMessage());
        }
    }

    /**
     * Format data specifically for PDF display
     */
    private function formatDataForPdf($data, $type)
    {
        if (empty($data)) {
            return [];
        }
        
        $formattedData = [];
        
        foreach ($data as $row) {
            $newRow = [];
            
            foreach ((array)$row as $key => $value) {
                // Format numeric values
                if (is_numeric($value) && !in_array($key, ['id', 'ID'])) {
                    if (stripos($key, 'amount') !== false || stripos($key, 'revenue') !== false || 
                        stripos($key, 'value') !== false || stripos($key, 'price') !== false) {
                        $newRow[$key] = $value; // Keep raw value for calculations in template
                    } elseif (stripos($key, 'rate') !== false || stripos($key, 'percentage') !== false) {
                        $newRow[$key] = $value; // Keep raw value for calculations in template
                    } else {
                        $newRow[$key] = $value;
                    }
                }
                // Format dates
                elseif ($value instanceof \DateTime || (is_string($value) && strtotime($value) !== false)) {
                    try {
                        $date = $value instanceof \DateTime ? $value : new \DateTime($value);
                        $newRow[$key] = $date->format('Y-m-d');
                    } catch (\Exception $e) {
                        $newRow[$key] = $value;
                    }
                }
                // Handle everything else
                else {
                    $newRow[$key] = $value;
                }
            }
            
            $formattedData[] = $newRow;
        }
        
        return $formattedData;
    }

    private function formatStatusForDisplay($status)
    {
        return match($status) {
            'pending_manager' => 'Pending RFQ Approver',
            'pending_customer' => 'Awaiting Customer Response',
            'pending_finance' => 'Pending LPO Admin Review',
            'completed' => 'Completed',
            'rejected' => 'Rejected',
            default => ucwords(str_replace('_', ' ', $status))
        };
    }

    private function arrayToCsv($data)
    {
        if (empty($data)) {
            return '';
        }

        // Create CSV string
        $output = fopen('php://temp', 'r+');
        
        // Add headers
        fputcsv($output, array_keys((array)$data[0]));
        
        // Add rows
        foreach ($data as $row) {
            fputcsv($output, (array)$row);
        }
        
        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);
        
        return $csv;
    }
}

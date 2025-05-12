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
        $this->middleware(['auth', 'role:manager']);
    }

    public function exportData(Request $request)
    {
        try {
            // Validate request
            $request->validate([
                'type' => 'required|in:quotes,marketers,products,performance,analytics',
                'format' => 'required|in:excel,csv,pdf',
                'dateFrom' => 'nullable|date',
                'dateTo' => 'nullable|date|after_or_equal:dateFrom',
                'marketer' => 'nullable|exists:users,id',
                'status' => 'nullable|string',
            ]);

            // Build query based on export type
            $data = $this->buildQuery($request);

            if ($data->isEmpty()) {
                return back()->with('export_error', 'No data available for the selected filters.');
            }

            // Format data based on export type
            $formattedData = $this->formatData($data, $request->type);

            // Generate response in requested format
            $response = $this->generateResponse($formattedData, $request->format, $request->type);

            // Flash success message to session
            session()->flash('export_success', ucfirst($request->type) . ' data exported successfully as ' . strtoupper($request->format));

            return $response;
        } catch (\Exception $e) {
            return back()->with('export_error', 'Error exporting data: ' . $e->getMessage());
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
                    ->with(['marketer', 'items'])
                    ->when($dateFrom, fn($q) => $q->whereDate('created_at', '>=', $dateFrom))
                    ->when($dateTo, fn($q) => $q->whereDate('created_at', '<=', $dateTo))
                    ->when($request->marketer, fn($q) => $q->where('marketer_id', $request->marketer))
                    ->when($request->status, fn($q) => $q->where('status', $request->status));
                break;

            case 'marketers':
                $query = User::role('marketer')
                    ->withCount(['quotes' => function($q) use ($dateFrom, $dateTo) {
                        $q->when($dateFrom, fn($q) => $q->whereDate('created_at', '>=', $dateFrom))
                          ->when($dateTo, fn($q) => $q->whereDate('created_at', '<=', $dateTo));
                    }])
                    ->withSum(['quotes' => function($q) use ($dateFrom, $dateTo) {
                        $q->when($dateFrom, fn($q) => $q->whereDate('created_at', '>=', $dateFrom))
                          ->when($dateTo, fn($q) => $q->whereDate('created_at', '<=', $dateTo));
                    }], 'total_amount');
                break;

            case 'products':
                $query = ProductItem::query()
                    ->withCount(['quotes' => function($q) use ($dateFrom, $dateTo) {
                        $q->when($dateFrom, fn($q) => $q->whereDate('created_at', '>=', $dateFrom))
                          ->when($dateTo, fn($q) => $q->whereDate('created_at', '<=', $dateTo));
                    }]);
                break;

            case 'performance':
                $query = DB::table('quotes')
                    ->join('users', 'quotes.marketer_id', '=', 'users.id')
                    ->join('quote_items', 'quotes.id', '=', 'quote_items.quote_id')
                    ->join('product_items', 'quote_items.product_item_id', '=', 'product_items.id')
                    ->select(
                        'users.name as marketer_name',
                        DB::raw('COUNT(DISTINCT quotes.id) as total_quotes'),
                        DB::raw('SUM(quotes.total_amount) as total_amount'),
                        DB::raw('AVG(quotes.total_amount) as average_quote_value'),
                        DB::raw('COUNT(DISTINCT product_items.id) as unique_products_sold')
                    )
                    ->when($dateFrom, fn($q) => $q->whereDate('quotes.created_at', '>=', $dateFrom))
                    ->when($dateTo, fn($q) => $q->whereDate('quotes.created_at', '<=', $dateTo))
                    ->when($request->marketer, fn($q) => $q->where('quotes.marketer_id', $request->marketer))
                    ->groupBy('users.id', 'users.name');
                break;

            case 'analytics':
                $query = Quote::selectRaw('
                    DATE(created_at) as date,
                    COUNT(*) as total_quotes,
                    SUM(total_amount) as total_revenue,
                    AVG(total_amount) as average_quote_value,
                    COUNT(DISTINCT marketer_id) as active_marketers
                ')
                ->when($dateFrom, fn($q) => $q->whereDate('created_at', '>=', $dateFrom))
                ->when($dateTo, fn($q) => $q->whereDate('created_at', '<=', $dateTo))
                ->when($request->marketer, fn($q) => $q->where('marketer_id', $request->marketer))
                ->groupBy(DB::raw('DATE(created_at)'))
                ->orderBy('date');
                break;
        }

        return $query->get();
    }

    private function formatData($data, $type)
    {
        $data = collect($data);
        
        switch ($type) {
            case 'quotes':
                return $data->map(fn($quote) => [
                    'ID' => $quote->id,
                    'Date' => $quote->created_at->format('Y-m-d'),
                    'Marketer' => $quote->marketer->name,
                    'Status' => $quote->status,
                    'Total Amount' => $quote->total_amount,
                    'Items Count' => $quote->items->count(),
                ]);

            case 'marketers':
                return $data->map(fn($user) => [
                    'ID' => $user->id,
                    'Name' => $user->name,
                    'Email' => $user->email,
                    'Total Quotes' => $user->quotes_count,
                    'Total Revenue' => $user->quotes_sum_total_amount,
                    'Average Quote Value' => $user->quotes_count ? 
                        $user->quotes_sum_total_amount / $user->quotes_count : 0,
                ]);

            case 'products':
                return $data->map(fn($item) => [
                    'ID' => $item->id,
                    'Name' => $item->name,
                    'Description' => $item->description,
                    'Price' => $item->price,
                    'Times Quoted' => $item->quotes_count,
                ]);

            // Performance and analytics data is already formatted by the query
            default:
                return $data;
        }
    }

    private function generateResponse($data, $format, $type)
    {
        $filename = sprintf('%s_export_%s', $type, now()->format('Y-m-d'));

        switch ($format) {
            case 'excel':
                return Excel::download(new DataExport($data), $filename . '.xlsx');

            case 'csv':
                return response($this->arrayToCsv($data))
                    ->header('Content-Type', 'text/csv')
                    ->header('Content-Disposition', "attachment; filename=\"$filename.csv\"");            case 'pdf':
                $pdf = Pdf::loadView('exports.pdf', [
                    'type' => $type,
                    'headers' => array_keys($data[0]),
                    'data' => $data,
                    'filters' => array_filter([
                        'date range' => request('dateFrom') && request('dateTo') ? 
                            request('dateFrom') . ' to ' . request('dateTo') : null,
                        'marketer' => request('marketer') ? User::find(request('marketer'))->name : null,
                        'status' => request('status'),
                    ])
                ]);
                return $pdf->download($filename . '.pdf');

            default:
                return response()->json($data);
        }
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

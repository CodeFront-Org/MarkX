<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            padding: 20px;
            font-size: 12px;
            color: #333;
            line-height: 1.4;
        }
        .header {
            margin-bottom: 30px;
            border-bottom: 2px solid #4a5568;
            padding-bottom: 15px;
        }
        .header h1 {
            color: #2d3748;
            font-size: 24px;
            margin-bottom: 5px;
        }
        .header p {
            color: #718096;
            font-size: 14px;
            margin-top: 0;
        }
        .filters {
            background-color: #f7fafc;
            padding: 10px 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            border-left: 4px solid #4299e1;
        }
        .filters h4 {
            margin-top: 0;
            margin-bottom: 10px;
            color: #2b6cb0;
            font-size: 14px;
        }
        .filters ul {
            list-style-type: none;
            padding-left: 0;
            margin: 0;
        }
        .filters ul li {
            margin-bottom: 5px;
            font-size: 12px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            font-size: 11px;
        }
        th {
            background-color: #4a5568;
            color: white;
            text-align: left;
            padding: 10px;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 10px;
            letter-spacing: 0.5px;
        }
        td {
            padding: 8px 10px;
            border-bottom: 1px solid #e2e8f0;
            vertical-align: top;
        }
        tr:nth-child(even) {
            background-color: #f7fafc;
        }
        tr:hover {
            background-color: #edf2f7;
        }
        .footer {
            margin-top: 30px;
            font-size: 10px;
            color: #718096;
            text-align: center;
            border-top: 1px solid #e2e8f0;
            padding-top: 10px;
        }
        .summary {
            margin-top: 20px;
            padding: 15px;
            background-color: #ebf8ff;
            border-radius: 5px;
        }
        .summary h3 {
            margin-top: 0;
            color: #2c5282;
            font-size: 14px;
        }
        .summary p {
            margin: 5px 0;
            font-size: 12px;
        }
        .numeric {
            text-align: right;
        }
        .text-success {
            color: #38a169;
        }
        .text-warning {
            color: #d69e2e;
        }
        .text-danger {
            color: #e53e3e;
        }
        .page-break {
            page-break-after: always;
        }
        .truncate {
            max-width: 150px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ ucfirst($type) }} Report</h1>
        <p>Generated on: {{ now()->format('Y-m-d H:i:s') }}</p>
    </div>

    @if(count($filters))
    <div class="filters">
        <h4>Filters Applied</h4>
        <ul>
            @foreach($filters as $key => $value)
                <li><strong>{{ ucfirst($key) }}:</strong> {{ $value }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    @if(count($data) > 0)
        <table>
            <thead>
                <tr>
                    @foreach($headers as $header)
                        <th>{{ ucwords(str_replace('_', ' ', $header)) }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach($data as $row)
                    <tr>
                        @foreach($row as $key => $cell)
                            @if(is_numeric($cell) && !in_array($key, ['id', 'ID']))
                                <td class="numeric">
                                    @if(stripos($key, 'amount') !== false || stripos($key, 'revenue') !== false || stripos($key, 'value') !== false || stripos($key, 'price') !== false)
                                        KES {{ number_format($cell, 2) }}
                                    @elseif(stripos($key, 'rate') !== false || stripos($key, 'percentage') !== false)
                                        {{ number_format($cell, 1) }}%
                                    @else
                                        {{ $cell }}
                                    @endif
                                </td>
                            @else
                                <td>{{ $cell }}</td>
                            @endif
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>

        @if(count($data) > 0)
            <div class="summary">
                <h3>Summary</h3>
                @if($type == 'performance' || $type == 'marketers')
                    <p><strong>Total Records:</strong> {{ count($data) }}</p>
                    @php
                        $totalRevenue = array_sum(array_column($data, 'total_amount') ?? array_column($data, 'Total Revenue') ?? [0]);
                        $totalQuotes = array_sum(array_column($data, 'total_quotes') ?? array_column($data, 'Total Quotes') ?? [0]);
                    @endphp
                    <p><strong>Total Revenue:</strong> KES {{ number_format($totalRevenue, 2) }}</p>
                    <p><strong>Total Quotes:</strong> {{ $totalQuotes }}</p>
                @elseif($type == 'quotes')
                    <p><strong>Total Quotes:</strong> {{ count($data) }}</p>
                    @php
                        $totalAmount = array_sum(array_column($data, 'Amount') ?? [0]);
                        $approvedCount = count(array_filter($data, function($item) {
                            return isset($item['Status']) && $item['Status'] == 'approved';
                        }));
                    @endphp
                    <p><strong>Total Amount:</strong> KES {{ number_format($totalAmount, 2) }}</p>
                    <p><strong>Approved Quotes:</strong> {{ $approvedCount }} ({{ $totalQuotes > 0 ? number_format(($approvedCount / count($data)) * 100, 1) : 0 }}%)</p>
                @elseif($type == 'items')
                    <p><strong>Total Items:</strong> {{ count($data) }}</p>
                    @php
                        $totalValue = array_sum(array_column($data, 'total_value') ?? [0]);
                        $totalQuantity = array_sum(array_column($data, 'total_quantity') ?? [0]);
                    @endphp
                    <p><strong>Total Value:</strong> KES {{ number_format($totalValue, 2) }}</p>
                    <p><strong>Total Quantity:</strong> {{ number_format($totalQuantity) }}</p>
                @endif
            </div>
        @endif
    @else
        <p>No data available for the selected filters.</p>
    @endif

    <div class="footer">
        <p>This is an automatically generated report. For questions, please contact the system administrator.</p>
        <p>© {{ date('Y') }} - All Rights Reserved</p>
    </div>
</body>
</html>

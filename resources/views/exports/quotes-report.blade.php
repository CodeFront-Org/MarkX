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
        .status {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
            display: inline-block;
        }
        .status-approved {
            background-color: #c6f6d5;
            color: #22543d;
        }
        .status-pending {
            background-color: #feebc8;
            color: #744210;
        }
        .status-rejected {
            background-color: #fed7d7;
            color: #742a2a;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 15px;
            margin-bottom: 20px;
        }
        .stat-card {
            background-color: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 5px;
            padding: 15px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        .stat-card h4 {
            margin-top: 0;
            margin-bottom: 5px;
            color: #4a5568;
            font-size: 12px;
            text-transform: uppercase;
        }
        .stat-card .value {
            font-size: 18px;
            font-weight: bold;
            color: #2d3748;
        }
        .pie-chart {
            width: 100%;
            max-width: 300px;
            margin: 0 auto;
            position: relative;
        }
        .pie-segment {
            position: absolute;
            width: 100%;
            height: 20px;
            margin-bottom: 5px;
        }
        .pie-label {
            display: inline-block;
            width: 120px;
            font-size: 11px;
        }
        .pie-value {
            display: inline-block;
            width: 60px;
            text-align: right;
            font-weight: bold;
            font-size: 11px;
        }
        .pie-bar {
            display: inline-block;
            height: 15px;
            background-color: #4299e1;
            margin-left: 10px;
            vertical-align: middle;
        }
        .pie-approved {
            background-color: #48bb78;
        }
        .pie-pending {
            background-color: #ecc94b;
        }
        .pie-rejected {
            background-color: #f56565;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Quotes Report</h1>
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
        <!-- Summary Stats -->
        <div class="summary">
            <h3>Quotes Summary</h3>
            <div class="stats-grid">
                <div class="stat-card">
                    <h4>Total Quotes</h4>
                    <div class="value">{{ count($data) }}</div>
                </div>
                <div class="stat-card">
                    <h4>Total Amount</h4>
                    @php
                        $totalAmount = array_sum(array_column($data, 'Amount'));
                    @endphp
                    <div class="value">KES {{ number_format($totalAmount, 2) }}</div>
                </div>
                <div class="stat-card">
                    <h4>Average Quote Value</h4>
                    @php
                        $avgValue = count($data) > 0 ? $totalAmount / count($data) : 0;
                    @endphp
                    <div class="value">KES {{ number_format($avgValue, 2) }}</div>
                </div>
            </div>
            
            <!-- Status Distribution -->
            @php
                $approved = count(array_filter($data, function($item) { return $item['Status'] === 'approved'; }));
                $pending = count(array_filter($data, function($item) { return $item['Status'] === 'pending'; }));
                $rejected = count(array_filter($data, function($item) { return $item['Status'] === 'rejected'; }));
                $total = count($data);
            @endphp
            <h4>Quote Status Distribution</h4>
            <div class="pie-chart">
                <div class="pie-segment">
                    <span class="pie-label">Approved</span>
                    <span class="pie-value">{{ $approved }} ({{ number_format(($approved / $total) * 100, 1) }}%)</span>
                    <span class="pie-bar pie-approved" style="width: {{ ($approved / $total) * 100 }}%;"></span>
                </div>
                <div class="pie-segment" style="top: 25px;">
                    <span class="pie-label">Pending</span>
                    <span class="pie-value">{{ $pending }} ({{ number_format(($pending / $total) * 100, 1) }}%)</span>
                    <span class="pie-bar pie-pending" style="width: {{ ($pending / $total) * 100 }}%;"></span>
                </div>
                <div class="pie-segment" style="top: 50px;">
                    <span class="pie-label">Rejected</span>
                    <span class="pie-value">{{ $rejected }} ({{ number_format(($rejected / $total) * 100, 1) }}%)</span>
                    <span class="pie-bar pie-rejected" style="width: {{ ($rejected / $total) * 100 }}%;"></span>
                </div>
            </div>
        </div>

        <!-- Quotes Table -->
        <h3>Quote Details</h3>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Date</th>
                    <th>Marketer</th>
                    <th>Status</th>
                    <th class="numeric">Amount</th>
                    <th class="numeric">Items</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data as $row)
                    <tr>
                        <td>{{ $row['ID'] }}</td>
                        <td>{{ $row['Date'] }}</td>
                        <td>{{ $row['Marketer'] }}</td>
                        <td>
                            <span class="status status-{{ strtolower($row['Status']) }}">
                                {{ $row['Status'] }}
                            </span>
                        </td>
                        <td class="numeric">KES {{ number_format($row['Amount'], 2) }}</td>
                        <td class="numeric">{{ $row['Items Count'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

    @else
        <p>No quotes data available for the selected filters.</p>
    @endif

    <div class="footer">
        <p>This is an automatically generated report. For questions, please contact the system administrator.</p>
        <p>© {{ date('Y') }} MarkX - All Rights Reserved</p>
    </div>
</body>
</html> 
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
        .rank {
            font-weight: bold;
            width: 30px;
            text-align: center;
        }
        .gold {
            color: #d69e2e;
        }
        .silver {
            color: #718096;
        }
        .bronze {
            color: #9c4221;
        }
        .medal {
            font-size: 14px;
            margin-right: 5px;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
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
        .bar-chart {
            width: 100%;
            margin-top: 20px;
        }
        .bar-container {
            height: 20px;
            background-color: #edf2f7;
            border-radius: 10px;
            margin-bottom: 10px;
            position: relative;
        }
        .bar {
            height: 100%;
            background-color: #4299e1;
            border-radius: 10px;
            position: absolute;
            top: 0;
            left: 0;
        }
        .bar-label {
            position: absolute;
            right: 10px;
            top: 2px;
            color: #2d3748;
            font-size: 10px;
            font-weight: bold;
        }
        .bar-name {
            font-size: 11px;
            margin-bottom: 2px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Marketer Performance Report</h1>
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
            <h3>Performance Summary</h3>
            <div class="stats-grid">
                <div class="stat-card">
                    <h4>Total Revenue</h4>
                    <div class="value">KES {{ number_format(array_sum(array_column($data, 'total_amount')), 2) }}</div>
                </div>
                <div class="stat-card">
                    <h4>Total Quotes</h4>
                    <div class="value">{{ array_sum(array_column($data, 'total_quotes')) }}</div>
                </div>
                <div class="stat-card">
                    <h4>Average Quote Value</h4>
                    @php
                        $totalQuotes = array_sum(array_column($data, 'total_quotes'));
                        $totalAmount = array_sum(array_column($data, 'total_amount'));
                        $avgValue = $totalQuotes > 0 ? $totalAmount / $totalQuotes : 0;
                    @endphp
                    <div class="value">KES {{ number_format($avgValue, 2) }}</div>
                </div>
                <div class="stat-card">
                    <h4>Unique Products Sold</h4>
                    <div class="value">{{ array_sum(array_column($data, 'unique_products_sold')) }}</div>
                </div>
            </div>
        </div>

        <!-- Performance Table -->
        <h3>Marketer Rankings</h3>
        <table>
            <thead>
                <tr>
                    <th>Rank</th>
                    <th>Marketer</th>
                    <th class="numeric">Total Quotes</th>
                    <th class="numeric">Total Amount</th>
                    <th class="numeric">Avg. Quote Value</th>
                    <th class="numeric">Products Sold</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data as $index => $row)
                    <tr>
                        <td class="rank">
                            @if($index === 0)
                                <span class="medal gold">🥇</span>
                            @elseif($index === 1)
                                <span class="medal silver">🥈</span>
                            @elseif($index === 2)
                                <span class="medal bronze">🥉</span>
                            @else
                                {{ $index + 1 }}
                            @endif
                        </td>
                        <td>{{ $row['marketer_name'] }}</td>
                        <td class="numeric">{{ number_format($row['total_quotes']) }}</td>
                        <td class="numeric">KES {{ number_format($row['total_amount'], 2) }}</td>
                        <td class="numeric">KES {{ number_format($row['average_quote_value'], 2) }}</td>
                        <td class="numeric">{{ number_format($row['unique_products_sold']) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Revenue Bar Chart -->
        <h3>Revenue Comparison</h3>
        <div class="bar-chart">
            @php
                $maxRevenue = max(array_column($data, 'total_amount'));
            @endphp
            @foreach(array_slice($data, 0, 5) as $row)
                <div class="bar-name">{{ $row['marketer_name'] }}</div>
                <div class="bar-container">
                    <div class="bar" style="width: {{ ($row['total_amount'] / $maxRevenue) * 100 }}%"></div>
                    <div class="bar-label">KES {{ number_format($row['total_amount'], 0) }}</div>
                </div>
            @endforeach
        </div>

    @else
        <p>No performance data available for the selected filters.</p>
    @endif

    <div class="footer">
        <p>This is an automatically generated report. For questions, please contact the system administrator.</p>
        <p>© {{ date('Y') }} MarkX - All Rights Reserved</p>
    </div>
</body>
</html> 
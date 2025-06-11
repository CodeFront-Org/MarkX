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
        .trend-chart {
            width: 100%;
            margin-top: 20px;
            position: relative;
            height: 200px;
            border-bottom: 1px solid #cbd5e0;
            border-left: 1px solid #cbd5e0;
        }
        .trend-point {
            position: absolute;
            width: 8px;
            height: 8px;
            background-color: #4299e1;
            border-radius: 50%;
            transform: translate(-4px, -4px);
        }
        .trend-line {
            position: absolute;
            height: 2px;
            background-color: #4299e1;
        }
        .trend-label {
            position: absolute;
            bottom: -25px;
            transform: translateX(-50%);
            font-size: 9px;
            color: #4a5568;
            text-align: center;
        }
        .trend-value {
            position: absolute;
            top: -20px;
            transform: translateX(-50%);
            font-size: 9px;
            color: #4a5568;
            font-weight: bold;
        }
        .axis-label {
            position: absolute;
            font-size: 10px;
            color: #718096;
        }
        .y-axis-label {
            transform: rotate(-90deg);
            left: -40px;
            top: 50%;
        }
        .x-axis-label {
            bottom: -40px;
            left: 50%;
            transform: translateX(-50%);
        }
        .highlight {
            color: #4299e1;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Analytics Report</h1>
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
            <h3>Analytics Summary</h3>
            <div class="stats-grid">
                <div class="stat-card">
                    <h4>Total Quotes</h4>
                    @php
                        $totalQuotes = array_sum(array_column($data, 'total_quotes'));
                    @endphp
                    <div class="value">{{ number_format($totalQuotes) }}</div>
                </div>
                <div class="stat-card">
                    <h4>Total Revenue</h4>
                    @php
                        $totalRevenue = array_sum(array_column($data, 'total_revenue'));
                    @endphp
                    <div class="value">KES {{ number_format($totalRevenue, 2) }}</div>
                </div>
                <div class="stat-card">
                    <h4>Average Quote Value</h4>
                    @php
                        $avgValue = $totalQuotes > 0 ? $totalRevenue / $totalQuotes : 0;
                    @endphp
                    <div class="value">KES {{ number_format($avgValue, 2) }}</div>
                </div>
            </div>
            
            <p>
                <strong>Date Range:</strong> 
                {{ min(array_column($data, 'date')) }} to {{ max(array_column($data, 'date')) }}
            </p>
            <p>
                <strong>Peak Day:</strong>
                @php
                    $peakDay = null;
                    $peakValue = 0;
                    foreach ($data as $day) {
                        if ($day['total_revenue'] > $peakValue) {
                            $peakValue = $day['total_revenue'];
                            $peakDay = $day;
                        }
                    }
                @endphp
                {{ $peakDay['date'] }} with <span class="highlight">KES {{ number_format($peakDay['total_revenue'], 2) }}</span> in revenue
                and <span class="highlight">{{ $peakDay['total_quotes'] }}</span> quotes
            </p>
        </div>

        <!-- Revenue Trend Chart -->
        <h3>Daily Revenue Trend</h3>
        <div class="trend-chart">
            @php
                $days = array_column($data, 'date');
                $revenues = array_column($data, 'total_revenue');
                $maxRevenue = max($revenues);
                $chartWidth = 100;
                $chartHeight = 180;
                $dayCount = count($days);
                $segmentWidth = $chartWidth / ($dayCount > 1 ? $dayCount - 1 : 1);
            @endphp
            
            <div class="axis-label y-axis-label">Revenue (KES)</div>
            <div class="axis-label x-axis-label">Date</div>
            
            @foreach($revenues as $i => $revenue)
                @php
                    $x = $i * $segmentWidth;
                    $y = $chartHeight - ($revenue / $maxRevenue * $chartHeight);
                    $y = max(0, min($y, $chartHeight)); // Ensure y is within bounds
                @endphp
                
                <!-- Draw point -->
                <div class="trend-point" style="left: {{ $x }}%; top: {{ $y }}px;"></div>
                
                <!-- Draw line to next point if not last point -->
                @if($i < count($revenues) - 1)
                    @php
                        $nextX = ($i + 1) * $segmentWidth;
                        $nextY = $chartHeight - ($revenues[$i + 1] / $maxRevenue * $chartHeight);
                        $nextY = max(0, min($nextY, $chartHeight)); // Ensure nextY is within bounds
                        
                        $lineLength = sqrt(pow($nextX - $x, 2) + pow($nextY - $y, 2));
                        $angle = atan2($nextY - $y, $nextX - $x) * 180 / pi();
                    @endphp
                    <div class="trend-line" style="
                        left: {{ $x }}%; 
                        top: {{ $y }}px; 
                        width: {{ $lineLength }}%; 
                        transform: rotate({{ $angle }}deg);
                        transform-origin: left center;
                    "></div>
                @endif
                
                <!-- Add date label for every 3rd point or if few points -->
                @if($i % 3 == 0 || $dayCount <= 10)
                    <div class="trend-label" style="left: {{ $x }}%;">
                        {{ \Carbon\Carbon::parse($days[$i])->format('M d') }}
                    </div>
                @endif
                
                <!-- Add value label for peaks or valleys -->
                @if(($i > 0 && $i < $dayCount - 1 && 
                    (($revenues[$i] > $revenues[$i-1] && $revenues[$i] > $revenues[$i+1]) || 
                     ($revenues[$i] < $revenues[$i-1] && $revenues[$i] < $revenues[$i+1]))) || 
                    $i == 0 || $i == $dayCount - 1 || $dayCount <= 5)
                    <div class="trend-value" style="left: {{ $x }}%; top: {{ $y }}px;">
                        {{ number_format($revenue / 1000, 0) }}K
                    </div>
                @endif
            @endforeach
        </div>

        <!-- Analytics Table -->
        <h3>Daily Analytics</h3>
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th class="numeric">Total Quotes</th>
                    <th class="numeric">Total Revenue</th>
                    <th class="numeric">Avg. Quote Value</th>
                    <th class="numeric">Active RFQ Processors</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data as $row)
                    <tr>
                        <td>{{ $row['date'] }}</td>
                        <td class="numeric">{{ number_format($row['total_quotes']) }}</td>
                        <td class="numeric">KES {{ number_format($row['total_revenue'], 2) }}</td>
                        <td class="numeric">KES {{ number_format($row['average_quote_value'], 2) }}</td>
                        <td class="numeric">{{ $row['active_rfq_processors'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

    @else
        <p>No analytics data available for the selected filters.</p>
    @endif

    <div class="footer">
        <p>This is an automatically generated report. For questions, please contact the system administrator.</p>
        <p>© {{ date('Y') }} MarkX - All Rights Reserved</p>
    </div>
</body>
</html> 
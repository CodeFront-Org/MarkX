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
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 200px;
        }
        .item-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 30px;
        }
        .item-card {
            background-color: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 5px;
            padding: 15px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        .item-card h4 {
            margin-top: 0;
            margin-bottom: 10px;
            color: #2d3748;
            font-size: 14px;
            border-bottom: 1px solid #e2e8f0;
            padding-bottom: 5px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .item-stats {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
        }
        .item-stat {
            margin-bottom: 5px;
        }
        .item-stat-label {
            font-size: 10px;
            color: #718096;
            text-transform: uppercase;
        }
        .item-stat-value {
            font-size: 14px;
            font-weight: bold;
            color: #2d3748;
        }
        .top-items {
            margin-bottom: 30px;
        }
        .price-range {
            display: flex;
            justify-content: space-between;
            margin-top: 15px;
            font-size: 11px;
            color: #718096;
        }
        .price-bar {
            height: 6px;
            background: linear-gradient(to right, #48bb78, #4299e1, #9f7aea);
            border-radius: 3px;
            margin: 5px 0;
        }
        .price-marker {
            width: 10px;
            height: 10px;
            background-color: #e53e3e;
            border-radius: 50%;
            position: absolute;
            transform: translate(-5px, -2px);
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Item Performance Report</h1>
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
            <h3>Item Performance Summary</h3>
            <div class="stats-grid">
                <div class="stat-card">
                    <h4>Total Items</h4>
                    <div class="value">{{ count($data) }}</div>
                </div>
                <div class="stat-card">
                    <h4>Total Quantity</h4>
                    @php
                        $totalQuantity = array_sum(array_column($data, 'total_quantity'));
                    @endphp
                    <div class="value">{{ number_format($totalQuantity) }}</div>
                </div>
                <div class="stat-card">
                    <h4>Total Value</h4>
                    @php
                        $totalValue = array_sum(array_column($data, 'total_value'));
                    @endphp
                    <div class="value">KES {{ number_format($totalValue, 2) }}</div>
                </div>
            </div>
        </div>

        <!-- Top Items by Value Chart -->
        <div class="top-items">
            <h3>Top 5 Items by Total Value</h3>
            <div class="bar-chart">
                @php
                    // Sort by total value and take top 5
                    $sortedItems = collect($data)->sortByDesc('total_value')->take(5);
                    $maxValue = $sortedItems->max('total_value');
                @endphp
                
                @foreach($sortedItems as $item)
                    <div class="bar-name">{{ $item['item'] }}</div>
                    <div class="bar-container">
                        <div class="bar" style="width: {{ ($item['total_value'] / $maxValue) * 100 }}%"></div>
                        <div class="bar-label">KES {{ number_format($item['total_value'], 0) }}</div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Featured Items -->
        <h3>Featured Items</h3>
        <div class="item-grid">
            @foreach(collect($data)->sortByDesc('total_value')->take(4) as $item)
                <div class="item-card">
                    <h4>{{ $item['item'] }}</h4>
                    <div class="item-stats">
                        <div class="item-stat">
                            <div class="item-stat-label">Total Quantity</div>
                            <div class="item-stat-value">{{ number_format($item['total_quantity']) }}</div>
                        </div>
                        <div class="item-stat">
                            <div class="item-stat-label">Average Price</div>
                            <div class="item-stat-value">KES {{ number_format($item['average_price'], 2) }}</div>
                        </div>
                        <div class="item-stat">
                            <div class="item-stat-label">Total Value</div>
                            <div class="item-stat-value">KES {{ number_format($item['total_value'], 2) }}</div>
                        </div>
                        <div class="item-stat">
                            <div class="item-stat-label">Total Count</div>
                            <div class="item-stat-value">{{ number_format($item['total_count']) }}</div>
                        </div>
                    </div>
                    
                    @php
                        // Calculate price position on the price range
                        $minPrice = collect($data)->min('average_price');
                        $maxPrice = collect($data)->max('average_price');
                        $priceRange = $maxPrice - $minPrice;
                        $pricePosition = $priceRange > 0 ? (($item['average_price'] - $minPrice) / $priceRange) * 100 : 50;
                    @endphp
                    
                    <div class="price-range">
                        <span>Min: KES {{ number_format($minPrice, 2) }}</span>
                        <span>Max: KES {{ number_format($maxPrice, 2) }}</span>
                    </div>
                    <div class="price-bar" style="position: relative;">
                        <div class="price-marker" style="left: {{ $pricePosition }}%;"></div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Items Table -->
        <h3>All Items</h3>
        <table>
            <thead>
                <tr>
                    <th>Item</th>
                    <th class="numeric">Total Count</th>
                    <th class="numeric">Total Quantity</th>
                    <th class="numeric">Average Price</th>
                    <th class="numeric">Total Value</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data as $row)
                    <tr>
                        <td>{{ $row['item'] }}</td>
                        <td class="numeric">{{ number_format($row['total_count']) }}</td>
                        <td class="numeric">{{ number_format($row['total_quantity']) }}</td>
                        <td class="numeric">KES {{ number_format($row['average_price'], 2) }}</td>
                        <td class="numeric">KES {{ number_format($row['total_value'], 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

    @else
        <p>No item data available for the selected filters.</p>
    @endif

    <div class="footer">
        <p>This is an automatically generated report. For questions, please contact the system administrator.</p>
        <p>© {{ date('Y') }} MarkX - All Rights Reserved</p>
    </div>
</body>
</html> 
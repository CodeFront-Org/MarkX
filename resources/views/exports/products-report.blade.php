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
        .product-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 30px;
        }
        .product-card {
            background-color: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 5px;
            padding: 15px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        .product-card h4 {
            margin-top: 0;
            margin-bottom: 10px;
            color: #2d3748;
            font-size: 14px;
            border-bottom: 1px solid #e2e8f0;
            padding-bottom: 5px;
        }
        .product-stats {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
        }
        .product-stat {
            margin-bottom: 5px;
        }
        .product-stat-label {
            font-size: 10px;
            color: #718096;
            text-transform: uppercase;
        }
        .product-stat-value {
            font-size: 14px;
            font-weight: bold;
            color: #2d3748;
        }
        .top-products {
            margin-bottom: 30px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Product Performance Report</h1>
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
            <h3>Product Summary</h3>
            <div class="stats-grid">
                <div class="stat-card">
                    <h4>Total Products</h4>
                    <div class="value">{{ count($data) }}</div>
                </div>
                <div class="stat-card">
                    <h4>Total Times Quoted</h4>
                    @php
                        $totalQuoted = array_sum(array_column($data, 'Times Quoted'));
                    @endphp
                    <div class="value">{{ number_format($totalQuoted) }}</div>
                </div>
                <div class="stat-card">
                    <h4>Average Price</h4>
                    @php
                        $avgPrice = array_sum(array_column($data, 'Price')) / count($data);
                    @endphp
                    <div class="value">KES {{ number_format($avgPrice, 2) }}</div>
                </div>
            </div>
        </div>

        <!-- Top Products Chart -->
        <div class="top-products">
            <h3>Top 5 Most Quoted Products</h3>
            <div class="bar-chart">
                @php
                    // Sort by times quoted and take top 5
                    $sortedProducts = collect($data)->sortByDesc('Times Quoted')->take(5);
                    $maxQuoted = $sortedProducts->max('Times Quoted');
                @endphp
                
                @foreach($sortedProducts as $product)
                    <div class="bar-name">{{ $product['Name'] }}</div>
                    <div class="bar-container">
                        <div class="bar" style="width: {{ ($product['Times Quoted'] / $maxQuoted) * 100 }}%"></div>
                        <div class="bar-label">{{ $product['Times Quoted'] }} quotes</div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Featured Products -->
        <h3>Featured Products</h3>
        <div class="product-grid">
            @foreach(collect($data)->sortByDesc('Times Quoted')->take(4) as $product)
                <div class="product-card">
                    <h4>{{ $product['Name'] }}</h4>
                    <div class="product-stats">
                        <div class="product-stat">
                            <div class="product-stat-label">ID</div>
                            <div class="product-stat-value">{{ $product['ID'] }}</div>
                        </div>
                        <div class="product-stat">
                            <div class="product-stat-label">Price</div>
                            <div class="product-stat-value">KES {{ number_format($product['Price'], 2) }}</div>
                        </div>
                        <div class="product-stat">
                            <div class="product-stat-label">Times Quoted</div>
                            <div class="product-stat-value">{{ number_format($product['Times Quoted']) }}</div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Products Table -->
        <h3>All Products</h3>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Description</th>
                    <th class="numeric">Price</th>
                    <th class="numeric">Times Quoted</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data as $row)
                    <tr>
                        <td>{{ $row['ID'] }}</td>
                        <td>{{ $row['Name'] }}</td>
                        <td>{{ $row['Description'] }}</td>
                        <td class="numeric">KES {{ number_format($row['Price'], 2) }}</td>
                        <td class="numeric">{{ number_format($row['Times Quoted']) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

    @else
        <p>No product data available for the selected filters.</p>
    @endif

    <div class="footer">
        <p>This is an automatically generated report. For questions, please contact the system administrator.</p>
        <p>© {{ date('Y') }} MarkX - All Rights Reserved</p>
    </div>
</body>
</html> 
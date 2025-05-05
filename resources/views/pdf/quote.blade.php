<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Quote #{{ $quote->id }}</title>
    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            margin: 0;
            padding: 20px;
            color: #2c3e50;
            line-height: 1.4;
        }
        .header {
            border-bottom: 3px solid #9b59b6;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        .header h1 {
            color: #2c3e50;
            margin: 0;
            font-size: 24px;
        }
        .company-info {
            float: left;
            width: 50%;
        }
        .quote-info {
            float: right;
            width: 50%;
            text-align: right;
        }
        .clear { clear: both; }
        .section { margin: 12px 0; }
        .quote-title {
            font-size: 20px;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 12px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 12px 0;
            font-size: 12px;
        }
        th {
            background-color: #f8f9fa;
            color: #2c3e50;
            font-weight: bold;
            padding: 6px;
            border-bottom: 2px solid #ddd;
            text-align: left;
        }
        td {
            padding: 6px;
            border-bottom: 1px solid #ddd;
        }
        .amount-summary {
            float: right;
            width: 250px;
            margin-top: 12px;
        }
        .amount-summary table { width: 100%; }
        .amount-summary td { padding: 4px; }
        .amount-summary .total {
            font-size: 14px;
            font-weight: bold;
            border-top: 2px solid #2c3e50;
        }
        .status-badge {
            display: inline-block;
            padding: 3px 6px;
            border-radius: 3px;
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .status-approved { background-color: #2ecc71; color: white; }
        .status-pending { background-color: #f1c40f; color: black; }
        .status-rejected { background-color: #e74c3c; color: white; }
        .item-status {
            display: inline-block;
            padding: 3px 6px;
            border-radius: 2px;
            font-size: 10px;
            text-transform: uppercase;
        }
        .item-approved { background-color: #2ecc71; color: white; }
        .item-pending { background-color: #f1c40f; color: black; }
        .validity-notice {
            background-color: #e8f6f3;
            border: 1px solid #2ecc71;
            padding: 8px;
            border-radius: 4px;
            margin: 12px 0;
            font-size: 11px;
        }
        .footer {
            margin-top: 20px;
            padding-top: 10px;
            border-top: 1px solid #ddd;
            text-align: center;
            font-size: 11px;
            color: #7f8c8d;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="company-info">
            <h1>{{ config('app.name') }}</h1>
            <p>Professional Business Solutions</p>
        </div>
        <div class="quote-info">
            <h2>QUOTATION</h2>
            <h3>#{{ $quote->id }}</h3>
        </div>
        <div class="clear"></div>
    </div>

    <div class="section">
        <div style="float: left; width: 50%;">
            <strong>Date Created:</strong> {{ $quote->created_at->format('F d, Y') }}<br>
            <strong>Valid Until:</strong> {{ $quote->valid_until->format('F d, Y') }}<br>
            <strong>Status:</strong> 
            <span class="status-badge status-{{ strtolower($quote->status) }}">
                {{ ucfirst($quote->status) }}
            </span>
        </div>
        <div class="clear"></div>
    </div>

    <div class="section">
        <div class="quote-title">{{ $quote->title }}</div>
        <p>{{ $quote->description }}</p>

        <table>
            <thead>
                <tr>
                    <th width="40%">Item Description</th>
                    <th width="15%">Quantity</th>
                    <th width="15%">Unit Price</th>
                    <th width="15%">Subtotal</th>
                    <th width="15%">Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($quote->items as $item)
                <tr>
                    <td>{{ $item->item }}</td>
                    <td>{{ $item->quantity }}</td>
                    <td>KES {{ number_format($item->price, 2) }}</td>
                    <td>KES {{ number_format($item->total, 2) }}</td>
                    <td>
                        <span class="item-status item-{{ $item->approved ? 'approved' : 'pending' }}">
                            {{ $item->approved ? 'Approved' : 'Pending' }}
                        </span>
                    </td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="3" style="text-align: right;"><strong>Total Amount:</strong></td>
                    <td colspan="2"><strong>KES {{ number_format($quote->amount, 2) }}</strong></td>
                </tr>
            </tfoot>
        </table>
    </div>

    <div class="validity-notice">
        <strong>Validity Period:</strong> This quote is valid until {{ $quote->valid_until->format('F d, Y') }}. 
        After this date, prices and availability may need to be reviewed.
    </div>

    <div class="footer">
        <p>Thank you for considering our services!</p>
        <p style="color: #95a5a6;">Generated on {{ now()->format('F d, Y H:i:s') }}</p>
    </div>
</body>
</html>
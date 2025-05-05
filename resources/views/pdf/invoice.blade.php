<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Invoice #{{ $invoice->invoice_number }}</title>
    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            margin: 0;
            padding: 20px;
            color: #2c3e50;
            line-height: 1.4;
        }

        .header {
            border-bottom: 3px solid #3498db;
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

        .invoice-info {
            float: right;
            width: 50%;
            text-align: right;
        }

        .clear {
            clear: both;
        }

        .section {
            margin: 12px 0;
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

        .amount-summary table {
            width: 100%;
        }

        .amount-summary td {
            padding: 4px;
        }

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
        <div class="invoice-info">
            <h2>INVOICE</h2>
            <h3>#{{ $invoice->invoice_number }}</h3>
        </div>
        <div class="clear"></div>
    </div>

    <div class="section">
        <div style="float: left; width: 50%;">
            <strong>Invoice Date:</strong> {{ $invoice->created_at->format('F d, Y') }}<br>
            <strong>Due Date:</strong> {{ $invoice->due_date->format('F d, Y') }}<br>
            <strong>Status:</strong> {{ ucfirst($invoice->status) }}
            @if($invoice->paid_at)
            <br><strong>Payment Date:</strong> {{ $invoice->paid_at->format('F d, Y') }}
            @endif
        </div>
        @if($invoice->quote)
        <div style="float: right; width: 50%; text-align: right;">
            <p><strong>Reference:</strong><br>
                Quote #{{ $invoice->quote->id }}: {{ $invoice->quote->title }}</p>
        </div>
        @endif
        <div class="clear"></div>
    </div>

    <div class="section">
        <table>
            <thead>
                <tr>
                    <th width="40%">Item Description</th>
                    <th width="15%">Quantity</th>
                    <th width="15%">Unit Price</th>
                    <th width="30%">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @foreach($invoice->quote->items()->where('approved', true)->get() as $item)
                <tr>
                    <td>{{ $item->item }}</td>
                    <td>{{ $item->quantity }}</td>
                    <td>KES {{ number_format($item->price, 2) }}</td>
                    <td>KES {{ number_format($item->total, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="3" style="text-align: right;"><strong>Total Amount:</strong></td>
                    <td><strong>KES {{ number_format($invoice->amount, 2) }}</strong></td>
                </tr>
            </tfoot>
        </table>
    </div>

    <div class="footer">
        <p>Thank you for your business!</p>
        @if($invoice->status !== 'paid')
        <p>Please make payment by the due date: {{ $invoice->due_date->format('F d, Y') }}</p>
        @endif
        <p style="color: #95a5a6;">Generated on {{ now()->format('F d, Y H:i:s') }}</p>
    </div>
</body>

</html>
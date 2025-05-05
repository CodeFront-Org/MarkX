<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice #{{ $invoice->invoice_number }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 40px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .invoice-info {
            margin-bottom: 20px;
        }
        .invoice-details {
            margin-bottom: 30px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f8f9fa;
        }
        .footer {
            margin-top: 50px;
            text-align: center;
            font-size: 12px;
        }
        .amount {
            font-size: 18px;
            font-weight: bold;
        }
        .payment-info {
            border: 1px solid #ddd;
            padding: 15px;
            margin-top: 20px;
            background-color: #f8f9fa;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Invoice #{{ $invoice->invoice_number }}</h1>
        <p>{{ config('app.name') }}</p>
    </div>

    <div class="invoice-info">
        <p><strong>Date:</strong> {{ $invoice->created_at->format('F d, Y') }}</p>
        <p><strong>Due Date:</strong> {{ $invoice->due_date->format('F d, Y') }}</p>
        <p><strong>Status:</strong> {{ ucfirst($invoice->status) }}</p>
        @if($invoice->paid_at)
            <p><strong>Paid Date:</strong> {{ $invoice->paid_at->format('F d, Y') }}</p>
        @endif
    </div>

    <div class="invoice-details">
        @if($invoice->quote)
            <h2>{{ $invoice->quote->title }}</h2>
            <div style="margin: 20px 0;">
                {!! nl2br(e($invoice->quote->description)) !!}
            </div>

            <table>
                <thead>
                    <tr>
                        <th>Item</th>
                        <th>Price</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($invoice->quote->items()->where('approved', true)->get() as $item)
                    <tr>
                        <td>{{ $item->item }}</td>
                        <td>KES {{ number_format($item->price, 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td style="text-align: right;"><strong>Total Amount:</strong></td>
                        <td><strong>KES {{ number_format($invoice->amount, 2) }}</strong></td>
                    </tr>
                </tfoot>
            </table>
        @endif
    </div>

    @if($invoice->payment_terms)
    <div class="payment-info">
        <h3>Payment Terms</h3>
        <div>
            {!! nl2br(e($invoice->payment_terms)) !!}
        </div>
    </div>
    @endif

    @if($invoice->notes)
    <div class="notes">
        <h3>Notes</h3>
        <div style="font-size: 12px;">
            {!! nl2br(e($invoice->notes)) !!}
        </div>
    </div>
    @endif

    <div class="footer">
        <p>Thank you for your business!</p>
        @if($invoice->status !== 'paid')
        <p>Please make payment by the due date: {{ $invoice->due_date->format('F d, Y') }}</p>
        @endif
        <p>Generated on {{ now()->format('F d, Y H:i:s') }}</p>
    </div>
</body>
</html>
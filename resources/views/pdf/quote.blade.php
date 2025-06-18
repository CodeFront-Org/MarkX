<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Quote #IVD{{ $quote->id }}</title>
    <style>
        @page {
            margin: 0;
            padding: 0;
        }
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            margin: 0;
            padding: 0;
            color: #2c3e50;
            line-height: 1.4;
            position: relative;
        }
        .background-letterhead {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1000;
        }
        .background-letterhead img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .content {
            position: relative;
            z-index: 100;
            padding: 20px;
            margin-top: 150px; /* Space for the letterhead header */
        }
        .header {
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
            background-color: rgba(255, 255, 255, 0.9); /* Semi-transparent background */
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
            background-color: rgba(232, 246, 243, 0.9);
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
            background-color: rgba(255, 255, 255, 0.9);
            padding: 10px;
            border-radius: 4px;
        }
    </style>
</head>
<body>
    <!-- Full-page background letterhead -->
    {{-- @if(isset($letterheadData) && isset($letterheadType))
    <div class="background-letterhead">
        <img src="data:{{ $letterheadType }};base64,{{ $letterheadData }}" alt="Company Letterhead">
    </div>
    @endif --}}


    <br />
    <div class="content" style="font-family: calibri, sans-serif; font-size: 20px; line-height: 1.5; width:85%; text-align: justify; margin: 0 auto;">
        <div class="header">
            <div class="quote-info">
                <h2>QUOTATION</h2>
                <h3>#IVD{{ $quote->reference ?? $quote->id }}</h3>
            </div>
            <div class="clear"></div>
        </div>

   
        
        <div class="section">
            <div class="quote-date">{{ $quote->created_at->format('F d, Y') }}</div><br>
            <div style="font-family: calibri, sans-serif; font-size: 30px; font-weight: bold;"> {{ $quote->title }} </div>
            {{ $quote->description }}<br><br>
            <b>Attn:{{ $quote->contact_person }}</b>
           

            <br>
            <br>

            <table style="font-family: calibri, sans-serif; font-size: 20px; line-height: 1.5;">                <thead>
                    <tr>
                        <th width="5%">No.</th>
                        <th width="20%">Item Description</th>
                        <th width="10%">Unit Pack</th>
                        <th width="10%">Quantity</th>
                        <th width="10%">Unit Price</th>
                        <th width="15%">Total</th>
                        <th width="10%">VAT Amount</th>
                        <th width="10%">Lead Time</th>
                        @if(!$showOnlyApproved && isset($showInternalDetails) && $showInternalDetails)
                        <th width="10%">Status</th>
                        @endif
                    </tr>
                </thead>
                <tbody >
                     @foreach($items as $item)
                    <tr>
                        <td>{{ $loop->index + 1 }}</td>
                        <td>{{ $item->item }}</td>
                        <td>{{ $item->unit_pack }}</td>
                        <td>{{ $item->quantity }}</td>
                        <td>KES {{ number_format($item->price, 2) }}</td>
                        <td>KES {{ number_format($item->quantity * $item->price, 2) }}</td>
                        <td>KES {{ number_format(($item->quantity * $item->price) * (($item->vat_rate ?? 16) / 100), 2) }}</td>
                        <td>{{ $item->lead_time }}</td>
                        @if(!$showOnlyApproved && isset($showInternalDetails) && $showInternalDetails)
                        <td>
                            @if($item->approved)
                            <span class="item-status item-approved">Approved</span>
                            @else
                            <span class="item-status item-pending">Pending</span>
                            @endif
                        </td>
                        @endif
                    </tr>
                    @endforeach
                </tbody>  
                <tfoot>
                    @if($showOnlyApproved)
                    <tr>
                        <td colspan="5" style="text-align: right;"><strong>Subtotal (Excl. VAT):</strong></td>
                        <td><strong>KES {{ number_format($approvedSubtotal ?? 0, 2) }}</strong></td>
                        <td colspan="2"></td>
                    </tr>
                    <tr>
                        <td colspan="5" style="text-align: right;"><strong>VAT Amount:</strong></td>
                        <td><strong>KES {{ number_format($approvedVatAmount ?? 0, 2) }}</strong></td>
                        <td colspan="2"></td>
                    </tr>
                    <tr class="total">
                        <td colspan="5" style="text-align: right;"><strong>Total Amount (Inc. VAT):</strong></td>
                        <td><strong>KES {{ number_format($approvedTotal ?? 0, 2) }}</strong></td>
                        <td colspan="2"></td>
                    </tr>
                    @else
                    <tr>
                        <td colspan="{{ (!$showOnlyApproved && isset($showInternalDetails) && $showInternalDetails) ? '5' : '5' }}" style="text-align: right;"><strong>Subtotal (Excl. VAT):</strong></td>
                        <td><strong>KES {{ number_format($itemsSubtotal ?? 0, 2) }}</strong></td>
                        <td colspan="2"></td>
                    </tr>
                    <tr>
                        <td colspan="{{ (!$showOnlyApproved && isset($showInternalDetails) && $showInternalDetails) ? '5' : '5' }}" style="text-align: right;"><strong>VAT Amount:</strong></td>
                        <td><strong>KES {{ number_format($itemsVatAmount ?? 0, 2) }}</strong></td>
                        <td colspan="2"></td>
                    </tr>
                    <tr class="total">
                        <td colspan="{{ (!$showOnlyApproved && isset($showInternalDetails) && $showInternalDetails) ? '5' : '5' }}" style="text-align: right;"><strong>Total Amount (Inc. VAT):</strong></td>
                        <td><strong>KES {{ number_format($itemsTotal ?? 0, 2) }}</strong></td>
                        <td colspan="2"></td>
                    </tr>
                    @if($hasUnapprovedItems && isset($showInternalDetails) && $showInternalDetails)
                    <tr>
                        <td colspan="5" style="text-align: right;"><strong>Approved Items Subtotal (Excl. VAT):</strong></td>
                        <td><strong>KES {{ number_format($approvedSubtotal ?? 0, 2) }}</strong></td>
                        <td colspan="2"></td>
                    </tr>
                    <tr>
                        <td colspan="5" style="text-align: right;"><strong>Approved Items VAT:</strong></td>
                        <td><strong>KES {{ number_format($approvedVatAmount ?? 0, 2) }}</strong></td>
                        <td colspan="2"></td>
                    </tr>
                    <tr class="total">
                        <td colspan="5" style="text-align: right;"><strong>Approved Items Total (Inc. VAT):</strong></td>
                        <td><strong>KES {{ number_format($approvedTotal ?? 0, 2) }}</strong></td>
                        <td colspan="2"></td>
                    </tr>
                    @endif
                    @endif
                </tfoot>
            </table>
        </div>

        {{-- <div class="validity-notice">
            <strong>Validity Period:</strong> This quote is valid until {{ $quote->valid_until->format('F d, Y') }}.
            After this date, prices and availability may need to be reviewed.
        </div> --}}
            <br>
            <br>
            <br>

        <div class="footerx" style="font-family: calibri, sans-serif; font-size: 20px;">
            <b>
            NB: Quotation valid for 30 days<br></b>
            We look forward to your order confirmation<br>
            Kind regards,<br>
            {{ $quote->user->name }}<br>
            {{-- <strong>Date:</strong> {{ $quote->created_at->format('F d, Y') }} --}}
            </b>
        </div>
    </div>
</body>
</html>
